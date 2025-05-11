<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SiteChecklist;
use App\Models\SiteChecklistSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

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

            $startOfWeek = Carbon::now()->startOfWeek(Carbon::SUNDAY)->startOfDay();
            $endOfWeek = Carbon::now()->endOfWeek(Carbon::SATURDAY)->endOfDay();

            // Get all rows in that date range
            $weeklyData = DB::table('site_checklist_data')
                ->join('xwalk_site_checklist_type', 'site_checklist_data.site_checklist_id', '=', 'xwalk_site_checklist_type.id')
                ->select(
                    'site_checklist_data.*',
                    'xwalk_site_checklist_type.checklist_type'
                )
                ->where('site_checklist_data.site_id', $site->id)
                ->whereBetween(DB::raw('DATE(site_checklist_data.log_date_time)'), [$startOfWeek, $endOfWeek])
                ->get();

            $tempValuesByDateAndShift = [];

            foreach ($weeklyData as $row) {
                $dayDateMap = json_decode($row->day_date_map, true);
                $checklistType = $row->checklist_type; // DAY or NIGHT SHIFT CHECKLIST

                foreach ($dayDateMap as $day => $date) {
                    if (!isset($tempValuesByDateAndShift[$day])) {
                        $tempValuesByDateAndShift[$day] = [];
                    }

                    // Support multiple entries per shift per day if needed
                    $tempValuesByDateAndShift[$day][$checklistType] = [
                        'temp_value' => $row->temp_value,
                        'staff_initial' => $row->staff_initial,
                    ];
                }
            }

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

            $checklistDataByTask = DB::table('site_checklist_data')
                ->where('site_id', $site_id)
                ->whereDate('week_start', $startOfWeek->toDateString())
                ->whereDate('week_end', $endOfWeek->toDateString())
                ->get()
                ->groupBy('site_checklist_id');
        } else {
            $day_shift_checklist = [];
            $night_shift_checklist = [];
            $checklist_data = [];
            $tempValuesByDateAndShift = [];
            $startOfWeek = null;
            $endOfWeek = null;
        }

        return view('employee.sitechecklist', [
            'day_shift_checklist' => $day_shift_checklist,
            'night_shift_checklist' => $night_shift_checklist,
            'checklistDataByTask' => $checklistDataByTask,
            'tempValuesByDateAndShift' => $tempValuesByDateAndShift,
            'weekStart' => $startOfWeek,
            'weekEnd' => $endOfWeek,
        ]);
    }
    public function indexAdmin(Request $request)
    {
        $sites = Site::all();
        $selectedSiteId = $request->site_id;

        // Force the week_start to be a Sunday
        $weekStart = $request->week_start
            ? Carbon::parse($request->week_start)->startOfWeek(Carbon::SUNDAY)
            : Carbon::now()->startOfWeek(Carbon::SUNDAY);

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SATURDAY);

        // Get all rows in that date range
        $weeklyData = DB::table('site_checklist_data')
            ->join('xwalk_site_checklist_type', 'site_checklist_data.site_checklist_id', '=', 'xwalk_site_checklist_type.id')
            ->select(
                'site_checklist_data.*',
                'xwalk_site_checklist_type.checklist_type'
            )
            ->where('site_checklist_data.site_id', $selectedSiteId)
            ->whereBetween(DB::raw('DATE(site_checklist_data.log_date_time)'), [$weekStart, $weekEnd])
            ->get();

        $tempValuesByDateAndShift = [];

        foreach ($weeklyData as $row) {
            $dayDateMap = json_decode($row->day_date_map, true);
            $checklistType = $row->checklist_type; // DAY or NIGHT SHIFT CHECKLIST

            foreach ($dayDateMap as $day => $date) {
                if (!isset($tempValuesByDateAndShift[$day])) {
                    $tempValuesByDateAndShift[$day] = [];
                }

                // Support multiple entries per shift per day if needed
                $tempValuesByDateAndShift[$day][$checklistType] = [
                    'temp_value' => $row->temp_value,
                    'staff_initial' => $row->staff_initial,
                ];
            }
        }

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

        $checklistDataByTask = DB::table('site_checklist_data')
            ->where('site_id', $selectedSiteId)
            ->whereDate('week_start', $weekStart->toDateString())
            ->whereDate('week_end', $weekEnd->toDateString())
            ->get()
            ->groupBy('site_checklist_id');

        return view('admin.site', compact(
            'sites',
            'selectedSiteId',
            'day_shift_checklist',
            'night_shift_checklist',
            'tempValuesByDateAndShift',
            'checklistDataByTask',
            'weekStart',
            'weekEnd'
        ));
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
            'site_checklist_ids' => 'required|array', // This will allow multiple checklist IDs
            'site_checklist_ids.*' => 'exists:xwalk_site_checklist_type,id', // Validate each ID
            'sun_bool' => 'nullable|boolean', // Each day is a boolean, not an array
            'mon_bool' => 'nullable|boolean',
            'tue_bool' => 'nullable|boolean',
            'wed_bool' => 'nullable|boolean',
            'thu_bool' => 'nullable|boolean',
            'fri_bool' => 'nullable|boolean',
            'sat_bool' => 'nullable|boolean',
            'temp_value' => 'nullable|string|max:255',
            'staff_initial' => 'nullable|string|max:255',
        ]);

        $site = auth()->user()->site;
        $now = now();

        $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        $startOfWeek = Carbon::parse($now)->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = $startOfWeek->copy()->addDays(6);

        $weekDates = collect(CarbonPeriod::create($startOfWeek, $endOfWeek))
            ->keyBy(fn($date) => strtolower($date->format('D')))
            ->map(fn($date) => $date->format('Y-m-d'));

        $checklistkeyedbyid = DB::table('xwalk_site_checklist_type')->pluck('task_name', 'id');

        // Check for existing logs within this week for each checklist
        foreach ($request->site_checklist_ids as $checklistId) {
            $existing = DB::table('site_checklist_data')
                ->where('site_checklist_id', $checklistId)
                ->where('site_id', $site->id)
                ->whereBetween('week_start', [$startOfWeek, $endOfWeek])
                ->get();

            // Validate if the selected day is already filled for the checklist in this week
            foreach ($days as $day) {
                $field = $day . '_bool';
                if ($request->$field && $existing->contains(fn($e) => $e->$field)) {
                    return back()->withErrors(["$field" => ucfirst($day) . " already filled for checklist $checklistkeyedbyid[$checklistId] in this week."]);
                }
            }
        }

        // Insert entries for each checklist
        foreach ($request->site_checklist_ids as $checklistId) {
            if($request->sun_bool || $request->mon_bool ||
             $request->tue_bool || $request->wed_bool ||
             $request->thu_bool || $request->fri_bool ||
             $request->sat_bool){
                // Map selected dates for each checklist
                $selectedDates = [];
                foreach ($days as $day) {
                    // If the day is selected (true), add it to the selectedDates array
                    if ($request->{$day . '_bool'}) {
                        $selectedDates[$day] = $weekDates[$day];
                    }
                }
            
                // Insert for each checklist ID
                DB::table('site_checklist_data')->insert([
                    'user_id' => auth()->id(),
                    'site_id' => $site->id,
                    'site_checklist_id' => $checklistId,
                    'sun_bool' => $request->sun_bool ?? 0,
                    'mon_bool' => $request->mon_bool ?? 0,
                    'tue_bool' => $request->tue_bool ?? 0,
                    'wed_bool' => $request->wed_bool ?? 0,
                    'thu_bool' => $request->thu_bool ?? 0,
                    'fri_bool' => $request->fri_bool ?? 0,
                    'sat_bool' => $request->sat_bool ?? 0,
                    'temp_value' => $request->temp_value,
                    'staff_initial' => $request->staff_initial,
                    'log_date_time' => $now,
                    'day_date_map' => json_encode($selectedDates), // Store the date mapping for selected days
                    'week_start' => $startOfWeek->format('Y-m-d'),
                    'week_end' => $endOfWeek->format('Y-m-d'),
                    'created_by' => auth()->id(),
                    'updated_by' => null,
                    'deleted_by' => null,
                    'is_deleted' => 0,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if($request->prev_sat_bool){
                $dateMap = [];
                $dateMap['sat'] =  $startOfWeek->subDay(1)->format('Y-m-d');
                $prev_week_start = Carbon::parse($dateMap['sat'])->startOfWeek(Carbon::SUNDAY);
                $prev_week_end = $prev_week_start->copy()->addDays(6);
                $log_date_time = $prev_week_end->format('Y-m-d').' '.'23:59:59';

                $existing = DB::table('site_checklist_data')
                ->where('site_checklist_id', $checklistId)
                ->where('site_id', $site->id)
                ->whereBetween('week_start', [$prev_week_start, $prev_week_end])
                ->get();

                $field = 'sat_bool';
                 if ($existing->contains(fn($e) => $e->$field)) {
                    return back()->withErrors(["$field" => ucfirst($day) . " already filled for checklist $checklistkeyedbyid[$checklistId] for the previous week night shift."]);
                }

                DB::table('site_checklist_data')->insert([
                'user_id' => auth()->id(),
                'site_id' => $site->id,
                'site_checklist_id' => $checklistId,
                'sun_bool' => 0,
                'mon_bool' => 0,
                'tue_bool' => 0,
                'wed_bool' => 0,
                'thu_bool' => 0,
                'fri_bool' => 0,
                'sat_bool' => 1,
                'temp_value' => $request->temp_value,
                'staff_initial' => $request->staff_initial,
                'log_date_time' => $log_date_time,
                'day_date_map' => json_encode($dateMap), // Store the date mapping for selected days
                'week_start' => $prev_week_start->format('Y-m-d'),
                'week_end' => $prev_week_end->format('Y-m-d'),
                'created_by' => auth()->id(),
                'updated_by' => null,
                'deleted_by' => null,
                'is_deleted' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            }
        }

        return redirect()->back()->with('success', 'Checklist entries saved successfully.');
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
