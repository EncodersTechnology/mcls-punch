<?php

namespace App\Http\Controllers;

use App\Models\FormData;
use App\Models\Resident;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $siteSearch = $request->input('site_search');
        $residentSearch = $request->input('resident_search');

        $sites = Site::query()
            ->when($siteSearch, fn ($q) => $q->where('name', 'like', "%$siteSearch%"))
            ->withCount('residents')
            ->orderBy('name')
            ->paginate(10, ['*'], 'sites_page')
            ->withQueryString();

        $residents = Resident::query()
            ->with('site:id,name')
            ->when($residentSearch, fn ($q) => $q->where('name', 'like', "%$residentSearch%"))
            ->when($request->filled('site_id'), fn ($q) => $q->where('site_id', $request->site_id))
            ->orderBy('name')
            ->paginate(10, ['*'], 'residents_page')
            ->withQueryString();

        // The modals need every site, not just the current page.
        $allSites = Site::orderBy('name')->get();

        return view('admin.site-resident', compact('sites', 'residents', 'allSites', 'siteSearch', 'residentSearch'));
    }

    /** Stream sites and their residents as CSV. */
    public function export(Request $request)
    {
        $filename = 'sites-and-residents-' . now()->format('Y-m-d-His') . '.csv';

        return response()->stream(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Site', 'Shift 1', 'Shift 2', 'Resident']);

            Site::with('residents')->orderBy('name')->chunk(100, function ($sites) use ($out) {
                foreach ($sites as $site) {
                    if ($site->residents->isEmpty()) {
                        fputcsv($out, [$site->name, $site->shift_1, $site->shift_2, '']);
                        continue;
                    }
                    foreach ($site->residents as $resident) {
                        fputcsv($out, [$site->name, $site->shift_1, $site->shift_2, $resident->name]);
                    }
                }
            });

            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-store, no-cache',
        ]);
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

        // Resident logs are clinical records and must not be destroyed with the site. The
        // foreign keys restrict this anyway; without the check it surfaces as a 500.
        $residentIds = Resident::where('site_id', $site->id)->pluck('id');
        $hasLogs = FormData::where('site_id', $site->id)
            ->orWhereIn('resident_id', $residentIds)
            ->exists();

        if ($hasLogs) {
            return redirect()->route('admin.resident')
                ->with('error', 'This site has resident logs recorded against it and cannot be deleted.');
        }

        // Delete the site
        $site->delete();

        // Redirect back with a success message
        $sites = Site::all();
        $residents = Resident::all();
        return redirect()->route('admin.resident')->with(['sites' => $sites, 'residents' => $residents, 'success' => 'Site Deleted Successfully']);
    }
}
