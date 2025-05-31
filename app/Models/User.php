<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
        return $this->hasOne(SiteUsers::class);
    }

    public function site()
    {
        return $this->hasOneThrough(Site::class, SiteUsers::class, 'user_id', 'id', 'id', 'site_id');
    }

    public function formDatas()
    {
        return $this->hasMany(FormData::class);
    }

    // Add these relationships to User model
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
        return $this->hasManyThrough(Site::class, SiteUsers::class, 'user_id', 'id', 'id', 'site_id');
    }

    public function canManage($user)
    {
        $hierarchy = [
            'superadmin' => 6,
            'admin' => 5,
            'director' => 4,
            'manager' => 3,
            'supervisor' => 2,
            'employee' => 1
        ];

        return $hierarchy[$this->usertype] > $hierarchy[$user->usertype];
    }

    public function getAccessibleSites()
    {
        switch ($this->usertype) {
            case 'superadmin':
            case 'admin':
            case 'director':
                return Site::all();
            case 'manager':
                $supervisorIds = $this->subordinates()->where('usertype', 'supervisor')->pluck('id');
                $supervisorSiteIds = SiteUsers::whereIn('user_id', $supervisorIds)->pluck('site_id');
                $managerSiteIds = $this->sites()->pluck('sites.id');
                return Site::whereIn('id', $supervisorSiteIds->merge($managerSiteIds))->get();
            case 'supervisor':
                return $this->sites;
            default:
                return $this->sites;
        }
    }
}
