<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;
use App\Models\SiteUsers;
use Illuminate\Http\Request;
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
        $users = User::where('usertype', '!=', 'admin')->with('site')->get();
        $sites = Site::with('siteUsers')->get();
        // dd($sites);
        return view('admin.users.index', compact('users', 'sites'));
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
        // Validate the form data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@multiculturalcls\.org$/'
            ],
            'password' => 'required|string|min:8|confirmed',  // Ensure password is confirmed
            'site_id' => 'required|exists:sites,id',
        ]);

        // If validation fails, redirect back with errors
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'usertype' => 'employee',
        ]);

        $site = SiteUsers::create([
            'user_id' => $user->id,
            'site_id' => $request->site_id,
        ]);

        // Redirect to a success page or the users list with a success message
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
    public function update(Request $request, SiteUsers $siteUsers, $id)
    {
        $user = User::findOrFail($request->id);
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id), // ignore current user for unique check
                'regex:/^[a-zA-Z0-9._%+-]+@multiculturalcls\.org$/'
            ],
            'password' => 'nullable|string|min:8|confirmed',  // Allow empty password field if not updating the password
            'site_id' => 'required|exists:sites,id',
        ]);

        // If validation fails, redirect back with errors and input data
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Find the user to be updated

        // Update the user's basic data
        $user->name = $request->name;
        $user->email = $request->email;

        // Update the password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Save the updated user data
        $user->save();

        // Update the user's associated site
        $siteUser = SiteUsers::where('user_id', $user->id)->first();
        if ($siteUser) {
            // If the site entry exists, update the site_id
            $siteUser->site_id = $request->site_id;
            $siteUser->save();
        } else {
            // If the site entry does not exist, create a new association
            SiteUsers::create([
                'user_id' => $user->id,
                'site_id' => $request->site_id,
            ]);
        }

        // Redirect to a relevant page with a success message
        return redirect()->route('site.access.index')->with('success', 'User updated successfully.');
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
