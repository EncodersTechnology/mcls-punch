<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormData;
use App\Models\Resident;
use App\Models\Site;
use App\Models\SiteChecklist;
use App\Models\SiteUsers;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class FormDataController extends Controller
{

    public function index()
    {
        // return latest form data
        $form_data = FormData::with('site')->orderByDesc('id')->first();
        $sites = Site::all();
        $site = SiteUsers::where('user_id', Auth::id())->with('site')->first();
        if ($site) {
            $site_residents = DB::table('residents')->where('site_id', $site->site_id)->get();
        } else {
            $site_residents = [];
        }
        $residents = Resident::all();
        return view('admin.new-dashboard', ['sites' => $sites, 'site' => $site, 'site_residents' => $site_residents, 'residents' => $residents, 'form_data' => $form_data]);
    }

    public function query(Request $request)
    {
        $site_id = $request->query('site_id');
        $resident_id = $request->query('resident_id');
        $form_data = FormData::when($site_id, function ($query) use ($site_id) {
            return $query->where('site_id', $site_id);
        })
            ->when($resident_id, function ($query) use ($resident_id) {
                return $query->where('resident_id', $resident_id);
            })
            ->with('site')
            ->orderByDesc('id')
            ->first();

        return response()->json(['data' => $form_data]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_type' => ['required', Rule::in(['mcls', 'agency'])],

                // Required if employee_type is mcls
                'mcls_name' => 'required_if:employee_type,mcls|nullable|string|max:255',
                'mcls_email' => [
                    'required_if:employee_type,mcls',
                    'nullable',
                    'email',
                    'regex:/^[a-zA-Z0-9._%+-]+@multiculturalcls\.org$/'
                ],

                // Required if employee_type is agency
                'agency_name' => 'required_if:employee_type,agency|nullable|string|max:255',
                'agency_employee_name' => 'required_if:employee_type,agency|nullable|string|max:255',

                'resident_id' => ['required', 'exists:residents,id'],

                'shift' => ['required'],
                'adls' => 'required|string',
                'medical' => 'required|string',
                'behavior' => 'required|string',
                'activities' => 'nullable|string',
                'nutrition' => 'nullable|string',
                'sleep' => 'nullable|string',
                'notes' => 'nullable|string',
                'temperature' => 'nullable|string',
                'log_date' => ['required', 'date'],
            ]);


            // Combine the submitted date with current time
            $submittedDate = Carbon::parse($validated['log_date'])->toDateString();
            $currentTime = now()->format('H:i:s');

            $validated['log_date'] = $submittedDate;
            $validated['log_time'] = $currentTime;

            $site = DB::table('site_users')->where('user_id', Auth::id())->first();
            $validated['site_id'] = $site->site_id;
            $validated['created_by'] = auth()->user()->id;
            $form_data = FormData::create($validated);
            $form_data['site'] = Site::where('id', $site->site_id)->first();
            return response()->json(['status' => true, 'data' => $form_data], 201);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Internal Server Error. ' . $e->getMessage()], 500);
        }
    }

    public function list(Request $request)
    {
        $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);
        $user_site = DB::table('site_users')->where('user_id', Auth::id())->first();
        $datas = [];
        $site = null;

        if ($user_site) {
            $site = Site::where('id', $user_site->site_id)->first();

            $query = FormData::where('site_id', $user_site->site_id);

            // Apply date filter
            if ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('log_date', [$request->from_date, $request->to_date]);
            }

            // Apply search filter on employee or resident name
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('mcls_name', 'like', "%$search%")
                        ->orWhere('agency_employee_name', 'like', "%$search%");
                });
            }

            $datas = $query->with(
                [
                    'createdBy:id,name',
                    'resident:id,name'
                ]
            )->get();
        }
        return view('employee.log', [
            'datas' => $datas,
            'site' => $site,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'search' => $request->search
        ]);
    }


    public function adminlog(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $site_id = $request->input('site_id');
        $resident_id = $request->input('resident_id');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        // Get all sites for the dropdown
        $sites = Site::all();

        // If site_id is present, get the residents for that site
        if ($site_id) {
            $residents = Resident::when($site_id, function ($query) use ($site_id) {
                $query->where('site_id', $site_id);
            })->get();
        } else {
            $residents = [];
        }

        // Build the query for FormData with the filters
        $datas = FormData::query()
            ->when($site_id, function ($query) use ($site_id) {
                $query->where('site_id', $site_id);
            })
            ->when($resident_id, function ($query) use ($resident_id) {
                $query->where('resident_id', $resident_id);
            })
            ->when($start_date, function ($query) use ($start_date) {
                $query->whereDate('log_date', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                $query->whereDate('log_date', '<=', $end_date);
            })
            ->with(
                [
                    'createdBy:id,name',
                    'resident:id,name'
                ]
            )
            ->get();

        // Return view with the data
        return view('admin.log', compact('datas', 'sites', 'residents', 'site_id', 'resident_id', 'start_date', 'end_date'));
    }



    public function residentform()
    {
        $site = DB::table('site_users')->where('user_id', Auth::id())->first();
        $checklistTypes = DB::table('xwalk_site_checklist_type')
            ->where('is_deleted', 0)
            ->where('status', 1)
            ->get()
            ->groupBy('checklist_type');

        $siteChecklistSettings = DB::table('site_checklist_settings as s')
            ->join('xwalk_site_checklist_type as x', 's.site_checklist_id', '=', 'x.id')
            ->select('s.*', 'x.checklist_type', 'x.group_name', 'x.task_name')
            ->where('s.is_deleted', 0)
            ->where('s.status', 1)
            ->where('x.is_deleted', 0)
            ->where('x.status', 1)
            ->where('site_id', $site->site_id)
            ->get();

        // Get current week's date range (Sun–Sat)
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = $startOfWeek->copy()->addDays(6);

        $prevSat = Carbon::parse($startOfWeek)->subDay(); // previous Saturday
        $weekDates = collect(CarbonPeriod::create($startOfWeek, $endOfWeek))
            ->prepend($prevSat) // add previous Saturday at the beginning
            ->keyBy(fn($date) => $date->eq($prevSat) ? 'prev_sat' : strtolower($date->format('D')))
            ->map(fn($date) => $date->format('Y-m-d'));
        // Get all rows in that date range
        $weeklyData = DB::table('site_checklist_data')
            ->where('site_id', $site->site_id)
            ->whereBetween(DB::raw('DATE(log_date_time)'), [$startOfWeek, $endOfWeek])
            ->get();

        $prev_sat_data = DB::table('site_checklist_data as s')
            ->join('xwalk_site_checklist_type as x', 's.site_checklist_id', '=', 'x.id')
            ->where('s.site_id', $site->site_id)
            ->where('s.sat_bool', 1)
            ->where('checklist_type', 'NIGHT SHIFT CHECKLIST')
            ->where(DB::raw('DATE(log_date_time)'), $prevSat->format('Y-m-d'))
            ->first();

        // Prepare final result: date => [temp_value, temp_value, ...]
        $tempValuesByDate = [];

        foreach ($weeklyData as $row) {
            $dayDateMap = json_decode($row->day_date_map, true);

            foreach ($dayDateMap as $day => $date) {
                if (!isset($tempValuesByDate[$day])) {
                    $tempValuesByDate[$day] = [];
                }
                $tempValuesByDate[$day] = $row->temp_value;
            }
        }

        if ($prev_sat_data) {
            $tempValuesByDate['prev_sat'] = $prev_sat_data->temp_value;
        }

        return view('employee.logform', [
            'checklistTypes' => $checklistTypes,
            'siteChecklistSettings' => $siteChecklistSettings,
            'weekDates' => $weekDates,
            'startOfWeek' => $startOfWeek->format('Y-m-d'),
            'endOfWeek' => $endOfWeek->format('Y-m-d'),
            'tempValuesByDate' => $tempValuesByDate
        ]);
    }
    public function show($id)
    {
        return response()->json(FormData::findOrFail($id));
    }

    public function update(Request $request, FormData $form_data)
    {
        $validated = $request->validate([
            'employee_type' => ['required', Rule::in(['mcls', 'agency'])],
            'mcls_name' => 'nullable|required_if:employee_type,mcls|string|max:255',
            'mcls_email' => 'nullable|required_if:employee_type,mcls|email|regex:/^[a-zA-Z0-9._%+-]+@multiculturalcls\.org$/',
            'agency_name' => 'nullable|required_if:employee_type,agency|string|max:255',
            'agency_employee_name' => 'nullable|required_if:employee_type,agency|string|max:255',
            'site' => 'required|string|max:255',
            'shift' => ['required', Rule::in(['morning', 'night'])],
            'resident_name' => 'required|string|max:255',
            'log_date' => 'required|date',
            'log_time' => 'required',
            'adls' => 'required|string',
            'medical' => 'required|string',
            'behavior' => 'required|string',
            'activities' => 'nullable|string',
            'nutrition' => 'nullable|string',
            'sleep' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $form_data_to_update->update($validated);
        return response()->json($form_data_to_update);
    }

    public function destroy($id)
    {
        $form_data_to_delete = FormData::where('id', $id)->firstOrFail();
        $form_data_to_delete->delete();
        return response()->json(['message' => 'Form Record deleted successfully']);
    }
}
