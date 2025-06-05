<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\FormData;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'usertype',
        'password',
        'manager_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function siteUser()
    {
        return $this->hasOne(SiteUsers::class, 'user_id');
    }

    public function site()
    {
        return $this->hasOneThrough(Site::class, SiteUsers::class, 'user_id', 'id', 'id', 'site_id');
    }

    public function formDatas()
    {
        return $this->hasMany(FormData::class, 'created_by');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function sites()
    {
        return $this->belongsToMany(Site::class, 'site_users', 'user_id', 'site_id');
    }

    /**
     * Assign a site to the user, ensuring Employees have only one site.
     *
     * @param int $siteId
     * @return void
     */
    public function assignSite($siteId)
    {
        if ($this->usertype === 'employee') {
            // Remove existing site associations for Employees
            $this->sites()->detach();
            $this->sites()->attach($siteId);
        } else {
            // Supervisors can have multiple sites
            $this->sites()->syncWithoutDetaching([$siteId]);
        }
    }

    /**
     * Remove a site from the user and update FormData if applicable.
     *
     * @param int $siteId
     * @param int|null $newSiteId
     * @return void
     */
    public function removeSite($siteId, $newSiteId = null)
    {
        if ($this->usertype === 'supervisor' && $newSiteId) {
            // Update FormData to new site
            FormData::where('site_id', $siteId)
                ->whereHas('createdBy', function ($query) {
                    $query->where('usertype', 'employee')
                          ->whereHas('sites', function ($q) {
                              $q->where('site_id', $this->sites()->pluck('site_id'));
                          });
                })
                ->update(['site_id' => $newSiteId]);
        }
        // Remove the site association
        $this->sites()->detach($siteId);
    }

    /**
     * Transfer a site to another Supervisor under the same Manager, updating FormData.
     *
     * @param int $siteId
     * @param User $toSupervisor
     * @return bool
     */
    public function transferSiteToSupervisor($siteId, User $toSupervisor)
    {
        if ($this->usertype !== 'supervisor' || $toSupervisor->usertype !== 'supervisor') {
            return false;
        }

        // Verify both Supervisors share the same Manager
        if ($this->manager_id !== $toSupervisor->manager_id) {
            return false;
        }

        // Update FormData to new Supervisor's site
        FormData::where('site_id', $siteId)
            ->whereHas('createdBy', function ($query) {
                $query->where('usertype', 'employee')
                      ->whereHas('sites', function ($q) {
                          $q->where('site_id', $this->sites()->pluck('site_id'));
                      });
            })
            ->update(['site_id' => $siteId]);

        // Remove site from current Supervisor
        $this->sites()->detach($siteId);
        // Assign site to target Supervisor
        $toSupervisor->assignSite($siteId);

        return true;
    }

    /**
     * Check if the user can manage another user or their data/sites.
     *
     * @param User $user
     * @return bool
     */
    public function canManage($user)
    {
        $hierarchy = [
            'admin' => 6,
            'siteadmin' => 5,
            'director' => 4,
            'manager' => 3,
            'supervisor' => 2,
            'employee' => 1,
        ];

        // Check hierarchy level
        if ($hierarchy[$this->usertype] <= $hierarchy[$user->usertype]) {
            return false;
        }

        // Specific rules based on usertype
        switch ($this->usertype) {
            case 'admin':
                return $user->usertype !== 'admin'; // Admin cannot manage other admins
            case 'siteadmin':
                return in_array($user->usertype, ['director', 'manager', 'supervisor', 'employee']);
            case 'director':
                return in_array($user->usertype, ['manager', 'supervisor']) &&
                       ($user->manager_id === $this->id || $this->subordinates()->pluck('id')->contains($user->id));
            case 'manager':
                return $user->usertype === 'supervisor' && $user->manager_id === $this->id;
            case 'supervisor':
                return $user->usertype === 'employee' &&
                       $user->sites()->pluck('site_id')->intersect($this->sites()->pluck('site_id'))->isNotEmpty();
            default:
                return false;
        }
    }

    /**
     * Get sites accessible to the user for management or data access.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAccessibleSites()
    {
        switch ($this->usertype) {
            case 'admin':
            case 'siteadmin':
                return Site::all(); // Admin and Siteadmin have access to all sites
            case 'director':
                // Directors access sites of their managers and supervisors
                $subordinateIds = $this->subordinates()->whereIn('usertype', ['manager', 'supervisor'])->pluck('id');
                return Site::whereHas('siteUsers', function ($query) use ($subordinateIds) {
                    $query->whereIn('user_id', $subordinateIds);
                })->get();
            case 'manager':
                // Managers access sites of their supervisors
                $supervisorIds = $this->subordinates()->where('usertype', 'supervisor')->pluck('id');
                return Site::whereHas('siteUsers', function ($query) use ($supervisorIds) {
                    $query->whereIn('user_id', $supervisorIds);
                })->get();
            case 'supervisor':
                return $this->sites; // Supervisors access their assigned sites
            case 'employee':
                // Employees access their single site
                return $this->sites()->take(1)->get();
            default:
                return collect(); // No access for other roles
        }
    }

    /**
     * Get FormData accessible to the user based on their role and sites.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getAccessibleFormData()
    {
        $accessibleSiteIds = $this->getAccessibleSites()->pluck('id');

        switch ($this->usertype) {
            case 'admin':
            case 'siteadmin':
                return FormData::query();
            case 'director':
            case 'manager':
            case 'supervisor':
                return FormData::whereIn('site_id', $accessibleSiteIds);
            case 'employee':
                return FormData::whereIn('site_id', $accessibleSiteIds)->where('created_by', $this->id);
            default:
                return FormData::where('id', 0); // No access
        }
    }
}