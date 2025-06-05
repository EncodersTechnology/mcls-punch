<?php

namespace App\Http\Controllers;

use App\Models\FormData;
use App\Models\Site;
use App\Models\User;
use App\Models\SiteUsers;
use Grosv\LaravelPasswordlessLogin\LoginUrl;
use Grosv\LaravelPasswordlessLogin\PasswordlessLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SiteUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentUser = User::where('id', auth()->user()->id)->first();

        // Restrict access - employees cannot see user lists
        if ($currentUser->usertype === 'employee') {
            return redirect()->back()->with('error', 'You do not have permission to view users.');
        }

        // Get users based on current user's permissions
        $users = $this->getUsersBasedOnRole($currentUser);

        // Get sites based on current user's access
        $sites = $currentUser->getAccessibleSites();

        // Get manageable user types
        $manageableUserTypes = $this->getManageableUserTypes($currentUser);

        return view('admin.users.index', compact('users', 'sites', 'manageableUserTypes'));
    }

    private function getUsersBasedOnRole($currentUser)
    {
        $accessibleSiteIds = $currentUser->getAccessibleSites()->pluck('id');

        switch ($currentUser->usertype) {
            case 'admin':
                // Admin can see all users except other admins
                return User::where('usertype', '!=', 'admin')->with('sites', 'manager')->get();
                
            case 'siteadmin':
                // Siteadmin can see directors, managers, supervisors, and employees of their sites
                return User::whereIn('usertype', ['director', 'manager', 'supervisor', 'employee'])
                    ->where(function ($query) use ($accessibleSiteIds) {
                        $query->whereIn('usertype', ['director', 'manager'])
                              ->orWhereHas('sites', function ($q) use ($accessibleSiteIds) {
                                  $q->whereIn('site_id', $accessibleSiteIds);
                              });
                    })
                    ->with('sites', 'manager')->get();
                    
            case 'director':
                // Director can only see managers and supervisors under them
                return User::whereIn('usertype', ['manager', 'supervisor'])
                    ->where(function ($query) use ($currentUser) {
                        $query->where('manager_id', $currentUser->id)
                            ->orWhereIn('manager_id', $currentUser->subordinates()->pluck('id'));
                    })->with('sites', 'manager')->get();
                    
            case 'manager':
                // Manager can see supervisors under them
                return User::where('usertype', 'supervisor')
                    ->where('manager_id', $currentUser->id)->with('sites', 'manager')->get();
                    
            case 'supervisor':
                // Supervisor can see employees of their site
                $supervisorSiteIds = $currentUser->sites->pluck('id');
                return User::where('usertype', 'employee')
                    ->whereHas('sites', function ($query) use ($supervisorSiteIds) {
                        $query->whereIn('site_id', $supervisorSiteIds);
                    })->with('sites', 'manager')->get();
                    
            default:
                return collect();
        }
    }

    private function getManageableUserTypes($currentUser)
    {
        switch ($currentUser->usertype) {
            case 'admin':
                // Admin can create siteadmin, director, manager, supervisor, employee
                return ['siteadmin', 'director', 'manager', 'supervisor', 'employee'];
                
            case 'siteadmin':
                // Siteadmin can create director, manager, supervisor, employee (not siteadmin)
                return ['director', 'manager', 'supervisor', 'employee'];
                
            case 'director':
                // Director can create manager, supervisor
                return ['manager', 'supervisor'];
                
            case 'manager':
                // Manager can create supervisor
                return ['supervisor'];
                
            case 'supervisor':
                // Supervisor can create employee
                return ['employee'];
                
            default:
                return [];
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $manageableTypes = $this->getManageableUserTypes($currentUser);

        // Prevent siteadmin from creating another siteadmin
        if ($currentUser->usertype === 'siteadmin' && $request->usertype === 'siteadmin') {
            return redirect()->back()->with('error', 'You do not have permission to create a siteadmin.')->withInput();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@multiculturalcls\.org$/'
            ],
            'password' => 'required|string|min:8|confirmed',
            'usertype' => ['required', Rule::in($manageableTypes)],
            'site_id' => 'required_if:usertype,supervisor,employee|exists:sites,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $managerId = $this->determineManagerId($request->usertype, $request->manager_id, $currentUser);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'usertype' => $request->usertype,
            'manager_id' => $managerId,
        ]);

        // Create site association for supervisor and employee
        if (in_array($request->usertype, ['supervisor', 'employee']) && $request->site_id) {
            SiteUsers::create([
                'user_id' => $user->id,
                'site_id' => $request->site_id,
            ]);
        }

        return redirect()->route('site.access.index')->with('success', 'User created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = Auth::user();

        if (!$currentUser->canManage($user)) {
            return redirect()->back()->with('error', 'You do not have permission to edit this user.');
        }

        // Prevent siteadmin from updating a user to siteadmin
        if ($currentUser->usertype === 'siteadmin' && $request->usertype === 'siteadmin') {
            return redirect()->back()->with('error', 'You do not have permission to update a user to siteadmin.')->withInput();
        }

        $manageableTypes = $this->getManageableUserTypes($currentUser);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
                'regex:/^[a-zA-Z0-9._%+-]+@multiculturalcls\.org$/'
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'usertype' => ['required', Rule::in($manageableTypes)],
            'site_id' => 'required_if:usertype,supervisor,employee|exists:sites,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $oldUsertype = $user->usertype;
        $managerId = $this->determineManagerId($request->usertype, $request->manager_id, $currentUser);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'usertype' => $request->usertype,
            'manager_id' => $managerId,
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        // Handle site associations
        $this->updateUserSiteAssociations($user, $request, $oldUsertype);

        return redirect()->route('site.access.index')->with('success', 'User updated successfully.');
    }

    private function determineManagerId($usertype, $requestManagerId, $currentUser)
    {
        switch ($usertype) {
            case 'siteadmin':
                // Siteadmin has no manager and can only be created by admin
                if ($currentUser->usertype !== 'admin') {
                    abort(403, 'Only admins can create siteadmin users.');
                }
                return null;
                
            case 'director':
                // Director's manager depends on who's creating them
                if ($currentUser->usertype === 'admin' || $currentUser->usertype === 'siteadmin') {
                    return $requestManagerId; // Can select a manager or none
                }
                return $currentUser->id;
                
            case 'manager':
                // Manager must have a director as manager
                if ($currentUser->usertype === 'director') {
                    return $currentUser->id;
                } elseif (in_array($currentUser->usertype, ['admin', 'siteadmin'])) {
                    return $requestManagerId; // Must select a director
                }
                return $requestManagerId;
                
            case 'supervisor':
                // Supervisor must have a manager as manager
                if ($currentUser->usertype === 'manager') {
                    return $currentUser->id;
                } else {
                    return $requestManagerId; // Must select a manager
                }
                
            case 'employee':
                // Employee doesn't need a manager in the hierarchy (site-based)
                return null;
                
            default:
                return null;
        }
    }

    private function updateUserSiteAssociations($user, $request, $oldUsertype)
    {
        // Remove existing site associations if user type changed from supervisor/employee
        if (
            in_array($oldUsertype, ['supervisor', 'employee']) &&
            !in_array($request->usertype, ['supervisor', 'employee'])
        ) {
            SiteUsers::where('user_id', $user->id)->delete();
            return;
        }

        // Handle site associations for supervisor/employee
        if (in_array($request->usertype, ['supervisor', 'employee']) && $request->site_id) {
            $siteUser = SiteUsers::where('user_id', $user->id)->first();

            if ($siteUser) {
                if ($siteUser->site_id != $request->site_id) {
                    // Update form data when site changes
                    FormData::where('site_id', $siteUser->site_id)
                        ->whereHas('site.siteUsers', function ($query) use ($user) {
                            $query->where('user_id', $user->id);
                        })
                        ->update(['site_id' => $request->site_id]);
                }
                $siteUser->update(['site_id' => $request->site_id]);
            } else {
                SiteUsers::create([
                    'user_id' => $user->id,
                    'site_id' => $request->site_id,
                ]);
            }
        }
    }

    public function transferSupervisorSites(Request $request)
    {
        $currentUser = Auth::user();

        // Only manager, director, siteadmin, and admin can transfer sites
        if (!in_array($currentUser->usertype, ['manager', 'director', 'siteadmin', 'admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to transfer sites.');
        }

        $validator = Validator::make($request->all(), [
            'from_supervisor_id' => 'required|exists:users,id',
            'to_supervisor_id' => 'required|exists:users,id',
            'site_ids' => 'required|array',
            'site_ids.*' => 'exists:sites,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $fromSupervisor = User::findOrFail($request->from_supervisor_id);
        $toSupervisor = User::findOrFail($request->to_supervisor_id);

        // Verify both users are supervisors
        if ($fromSupervisor->usertype !== 'supervisor' || $toSupervisor->usertype !== 'supervisor') {
            return redirect()->back()->with('error', 'Both users must be supervisors.');
        }

        // Verify the current user has access to the sites being transferred
        $accessibleSiteIds = $currentUser->getAccessibleSites()->pluck('id');
        if (!collect($request->site_ids)->every(fn($siteId) => $accessibleSiteIds->contains($siteId))) {
            return redirect()->back()->with('error', 'You do not have permission to transfer one or more selected sites.');
        }

        // Transfer site associations
        foreach ($request->site_ids as $siteId) {
            SiteUsers::where('user_id', $fromSupervisor->id)
                ->where('site_id', $siteId)
                ->update(['user_id' => $toSupervisor->id]);
        }

        return redirect()->back()->with('success', 'Sites transferred successfully.');
    }

    public function getSupervisorSites($id)
    {
        $supervisor = User::findOrFail($id);
        
        // Verify user is a supervisor
        if ($supervisor->usertype !== 'supervisor') {
            return response()->json(['error' => 'User is not a supervisor'], 400);
        }
        
        $sites = $supervisor->sites;
        return response()->json($sites);
    }

   function magicLogin($id)
    {
        $user = User::find($id);
        $generator = new LoginUrl($user);
        $generator->setRedirectUrl('/dashboard'); // Override the default url to redirect to after login
        $url = $generator->generate();

        //OR Use a Facade
        $url = PasswordlessLogin::forUser($user)->generate();

        return redirect($url)->with(Auth::logout());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $currentUser = Auth::user();

        // Check if current user can manage this user
        if (!$currentUser->canManage($user)) {
            return redirect()->back()->with('error', 'You do not have permission to delete this user.');
        }

        // Delete related SiteUsers record
        SiteUsers::where('user_id', $id)->delete();

        // Delete the user
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}