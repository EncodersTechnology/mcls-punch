<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sites = Site::all();
        $residents = Resident::all();
        return view('admin.site-resident', compact('sites', 'residents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
    
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        Site::create($validated);
        $sites = Site::all();
        $residents = Resident::all();
        return redirect()->route('admin.resident')->with(['sites' => $sites, 'residents' => $residents, 'success' => 'Site Created Successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Site $site)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Site $site)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Site $site)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
    
        $site->update($validated);
        $sites = Site::all();
        $residents = Resident::all();
        return redirect()->route('admin.resident')->with(['sites' => $sites, 'residents' => $residents, 'success' => 'Site Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Find the site by ID
        $site = Site::findOrFail($id);

        // Delete the site
        $site->delete();

        // Redirect back with a success message
        $sites = Site::all();
        $residents = Resident::all();
        return redirect()->route('admin.resident')->with(['sites' => $sites, 'residents' => $residents, 'success' => 'Site Deleted Successfully']);

    }
}
