<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SiteUsers;
use App\Models\User;
use Illuminate\Http\Request;

class SiteUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::where('usertype','!=','admin')->with('site')->get();
        $sites = Site::all();
        return view('admin.users.index',compact('users','sites'));
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
        //
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
    public function update(Request $request, SiteUsers $siteUsers)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SiteUsers $siteUsers)
    {
        //
    }
}
