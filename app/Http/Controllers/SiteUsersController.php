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
        // $currentUser = Auth::user();

        $currentUser = User::where('id', auth()->user()->id)->first();

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
        switch ($currentUser->usertype) {
            case 'admin':
                return User::where('usertype', '!=', 'admin')->with('site', 'manager')->get();
            case 'siteadmin':
                return User::whereIn('usertype', ['director', 'manager', 'supervisor', 'employee'])->with('site', 'manager')->get();
            case 'director':
                return User::whereIn('usertype', ['manager', 'supervisor', 'employee'])
                    ->where(function ($query) use ($currentUser) {
                        $query->where('manager_id', $currentUser->id)
                            ->orWhereIn('manager_id', $currentUser->subordinates()->pluck('id'));
                    })->with('site', 'manager')->get();
            case 'manager':
                return User::whereIn('usertype', ['supervisor', 'employee'])
                    ->where('manager_id', $currentUser->id)->with('site', 'manager')->get();
            default:
                return collect();
        }
    }

    private function getManageableUserTypes($currentUser)
    {
        switch ($currentUser->usertype) {
            case 'admin':
                return ['siteadmin', 'director', 'manager', 'supervisor', 'employee'];
            case 'siteadmin':
                return ['director', 'manager', 'supervisor', 'employee'];
            case 'director':
                return ['manager', 'supervisor', 'employee'];
            case 'manager':
                return ['supervisor', 'employee'];
            default:
                return [];
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $manageableTypes = $this->getManageableUserTypes($currentUser);

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

        // Create site association if needed
        if (in_array($request->usertype, ['supervisor', 'employee']) && $request->site_id) {
            SiteUsers::create([
                'user_id' => $user->id,
                'site_id' => $request->site_id,
            ]);
        }

        return redirect()->route('site.access.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SiteUsers $siteUsers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SiteUsers $siteUsers)
    {
        //
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
            case 'director':
                return $currentUser->usertype === 'admin' ? null : $currentUser->id;
            case 'manager':
                return $currentUser->usertype === 'director' ? $currentUser->id : $requestManagerId;
            case 'supervisor':
                return $requestManagerId ?: $currentUser->id;
            case 'employee':
                return $requestManagerId ?: $currentUser->id;
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
    public function destroy(SiteUsers $siteUsers, $id)
    {
        $user = User::findOrFail($id);

        // Delete related SiteUsers record
        SiteUsers::where('user_id', $id)->delete();

        // Delete the user
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
