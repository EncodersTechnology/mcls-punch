<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SiteChecklist;
use App\Models\SiteChecklistSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;

class SiteChecklistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $site = auth()->user()->site;
        if ($site) {
            $site_id = $site->id;

            $day_shift_checklist = DB::table('site_checklist_settings')
                ->select('site_checklist_settings.*', 'xwalk_site_checklist_type.*')
                ->join('xwalk_site_checklist_type', 'site_checklist_settings.site_checklist_id', '=', 'xwalk_site_checklist_type.id')
                ->where('site_checklist_settings.site_id', $site_id)
                ->where('xwalk_site_checklist_type.checklist_type', 'DAY SHIFT CHECKLIST')
                ->get()
                ->groupBy('group_name');

            $night_shift_checklist = DB::table('site_checklist_settings')
                ->select('site_checklist_settings.*', 'xwalk_site_checklist_type.*')
                ->join('xwalk_site_checklist_type', 'site_checklist_settings.site_checklist_id', '=', 'xwalk_site_checklist_type.id')
                ->where('site_checklist_settings.site_id', $site_id)
                ->where('xwalk_site_checklist_type.checklist_type', 'NIGHT SHIFT CHECKLIST')
                ->get()
                ->groupBy('group_name');

            $checklist_data = DB::table('site_checklist_data')
                ->where('site_id', $site_id)
                ->get()
                ->keyBy('site_checklist_id');
        }
        else{
            $day_shift_checklist = [];
            $night_shift_checklist = [];
            $checklist_data = [];
        }

        return view('employee.sitechecklist', [
            'day_shift_checklist' => $day_shift_checklist,
            'night_shift_checklist' => $night_shift_checklist,
            'checklist_data' => $checklist_data,
        ]);
    }

    public function indexAdmin(Request $request)
    {
        $sites = Site::all();

        $selectedSiteId = $request->site_id;

        $day_shift_checklist = collect();
        $night_shift_checklist = collect();

        if ($selectedSiteId) {
            $day_shift_checklist = DB::table('site_checklist_settings')
                ->select('site_checklist_settings.*', 'xwalk_site_checklist_type.*')
                ->join('xwalk_site_checklist_type', 'site_checklist_settings.site_checklist_id', '=', 'xwalk_site_checklist_type.id')
                ->where('site_checklist_settings.site_id', $selectedSiteId)
                ->where('xwalk_site_checklist_type.checklist_type', 'DAY SHIFT CHECKLIST')
                ->get()
                ->groupBy('group_name');

            $night_shift_checklist = DB::table('site_checklist_settings')
                ->select('site_checklist_settings.*', 'xwalk_site_checklist_type.*')
                ->join('xwalk_site_checklist_type', 'site_checklist_settings.site_checklist_id', '=', 'xwalk_site_checklist_type.id')
                ->where('site_checklist_settings.site_id', $selectedSiteId)
                ->where('xwalk_site_checklist_type.checklist_type', 'NIGHT SHIFT CHECKLIST')
                ->get()
                ->groupBy('group_name');
        }

        return view('admin.site', compact('sites', 'selectedSiteId', 'day_shift_checklist', 'night_shift_checklist'));
    }

    public function settings(Request $request)
    {
        $sites = Site::all();
        $selectedSiteId = $request->get('site_id');

        $day_shift_checklist = collect();
        $night_shift_checklist = collect();

        if ($selectedSiteId) {
            $checklists = DB::table('xwalk_site_checklist_type')->get();

            $day_shift_checklist = $checklists->where('checklist_type', 'DAY SHIFT CHECKLIST')
                ->groupBy('group_name');

            $night_shift_checklist = $checklists->where('checklist_type', 'NIGHT SHIFT CHECKLIST')
                ->groupBy('group_name');

            // Optional: preload checklist settings for the site
        }

        return view('admin.checklistsettings', compact('sites', 'selectedSiteId', 'day_shift_checklist', 'night_shift_checklist'));
    }

    public function toggleSetting(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|integer',
            'task_id' => 'required|integer',
            'day' => 'required|string|in:sun,mon,tue,wed,thu,fri,sat',
            'enabled' => 'required|boolean',
        ]);

        $setting = SiteChecklistSetting::firstOrCreate([
            'site_id' => $data['site_id'],
            'site_checklist_id' => $data['task_id']
        ]);

        $setting->update([
            $data['day'] . '_enabled_bool' => $data['enabled']
        ]);

        return response()->json(['success' => true]);
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
        $validated = $request->validate([
            'site_checklist_id' => 'required|exists:xwalk_site_checklist_type,id',
            'sun_bool' => 'nullable|boolean',
            'mon_bool' => 'nullable|boolean',
            'tue_bool' => 'nullable|boolean',
            'wed_bool' => 'nullable|boolean',
            'thu_bool' => 'nullable|boolean',
            'fri_bool' => 'nullable|boolean',
            'sat_bool' => 'nullable|boolean',
            'temp_value' => 'nullable|string|max:255',
        ]);

        $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        $existing = DB::table('site_checklist_data')
            ->where('site_checklist_id', $request->site_checklist_id)
            ->first();

        if ($existing) {
            foreach ($days as $day) {
                $field = $day . '_bool';
                if ($request->$field && $existing->$field) {
                    return back()->withErrors(["$field" => ucfirst($day) . " already filled for this checklist."]);
                }
            }
        }

        // Insert data
        DB::table('site_checklist_data')->insert([
            'user_id' => auth()->id(),
            'site_id' => optional(auth()->user())->site->id,
            'site_checklist_id' => $request->site_checklist_id,
            'sun_bool' => $request->sun_bool ?? 0,
            'mon_bool' => $request->mon_bool ?? 0,
            'tue_bool' => $request->tue_bool ?? 0,
            'wed_bool' => $request->wed_bool ?? 0,
            'thu_bool' => $request->thu_bool ?? 0,
            'fri_bool' => $request->fri_bool ?? 0,
            'sat_bool' => $request->sat_bool ?? 0,
            'temp_value' => $request->temp_value,
            'log_date_time' => $request->log_date_time,
            'created_by' => auth()->id(),
            'updated_by' => null,
            'deleted_by' => null,
            'is_deleted' => 0,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Checklist entry saved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SiteChecklist $siteChecklist)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SiteChecklist $siteChecklist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SiteChecklist $siteChecklist)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SiteChecklist $siteChecklist)
    {
        //
    }
}
