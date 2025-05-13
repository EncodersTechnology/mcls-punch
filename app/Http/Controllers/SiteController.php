<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function create(Request $request) {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'shift_1' => 'required|string|max:255',
            'shift_2' => 'required|string|max:255',
        ]);

        $site = Site::create($validated);

        $checklistTypes = DB::table('xwalk_site_checklist_type')->get();

        foreach ($checklistTypes as $type) {
            DB::table('site_checklist_settings')->insert([
                'site_id' => $site->id,
                'site_checklist_id' => $type->id,
                'sun_enabled_bool' => 1,
                'mon_enabled_bool' => 1,
                'tue_enabled_bool' => 1,
                'wed_enabled_bool' => 1,
                'thu_enabled_bool' => 1,
                'fri_enabled_bool' => 1,
                'sat_enabled_bool' => 1,
                'created_by' => auth()->id(),
                'updated_by' => null,
                'deleted_by' => null,
                'is_deleted' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $sites = Site::all();
        $residents = Resident::all();

        return redirect()->route('admin.resident')->with([
            'sites' => $sites,
            'residents' => $residents,
            'success' => 'Site Created Successfully'
        ]);
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
            'shift_1' => 'required|string|max:255',
            'shift_2' => 'required|string|max:255',
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
