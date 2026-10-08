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
        $accessibleSiteIds = Auth::user()->getAccessibleSites()->pluck('id');

        // Latest log the current user is actually allowed to see. Unscoped, this showed
        // the newest clinical record in the organisation to every user.
        $form_data = FormData::with('site')
            ->whereIn('site_id', $accessibleSiteIds)
            ->orderByDesc('id')
            ->first();

        $sites = Site::whereIn('id', $accessibleSiteIds)->get();
        $site = SiteUsers::where('user_id', Auth::id())->with('site')->first();
        if ($site) {
            $site_residents = DB::table('residents')->where('site_id', $site->site_id)->get();
        } else {
            $site_residents = [];
        }

        return view('admin.new-dashboard', ['sites' => $sites, 'site' => $site, 'site_residents' => $site_residents, 'form_data' => $form_data]);
    }

    public function query(Request $request)
    {
        $site_id = $request->query('site_id');
        $resident_id = $request->query('resident_id');
        $accessibleSiteIds = Auth::user()->getAccessibleSites()->pluck('id');

        $form_data = FormData::whereIn('site_id', $accessibleSiteIds)
            ->when($site_id, function ($query) use ($site_id) {
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
            $site = DB::table('site_users')->where('user_id', Auth::id())->first();
            if (!$site) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account is not assigned to a site, so a log cannot be created.',
                ], 422);
            }

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

                // The resident must belong to the site the log is being filed under.
                'resident_id' => ['required', Rule::exists('residents', 'id')->where('site_id', $site->site_id)],

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

        return view('employee.log', $this->logScreenData($request));
    }

    public function adminlog(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        return view('admin.log', $this->logScreenData($request));
    }

    /**
     * Both log screens show the same thing to different audiences, so they share one
     * query, one filter set and one payload. Access scoping lives in scopedLogQuery().
     */
    private function logScreenData(Request $request): array
    {
        $sites = Auth::user()->getAccessibleSites();
        $accessibleSiteIds = $sites->pluck('id');

        $datas = $this->scopedLogQuery($request)
            ->with(['createdBy:id,name', 'resident:id,name', 'site:id,name'])
            ->orderByDesc('log_date')->orderByDesc('id')
            ->paginate($this->perPage($request))->withQueryString();

        // Resident filter options follow the chosen site, falling back to every resident
        // at a site the user can reach.
        $residents = Resident::query()
            ->when(
                $request->filled('site_id') && $accessibleSiteIds->contains($request->site_id),
                fn ($q) => $q->where('site_id', $request->site_id),
                fn ($q) => $q->whereIn('site_id', $accessibleSiteIds)
            )
            ->orderBy('name')
            ->get();

        return compact('datas', 'sites', 'residents');
    }

  public function updateLogData(Request $request, $id)
    {
        $formData = FormData::findOrFail($id);

        if (Auth::user()->usertype !== 'admin') {
            $accessibleSiteIds = Auth::user()->getAccessibleSites()->pluck('id');

            // The record being edited must belong to a site the user can access.
            if (!$accessibleSiteIds->contains($formData->site_id)) {
                return redirect()->back()->withErrors(['site_id' => 'You do not have access to this log entry.']);
            }

            // If the site is being changed, the target site must be accessible too.
            if ($request->filled('site_id') && !$accessibleSiteIds->contains($request->site_id)) {
                return redirect()->back()->withErrors(['site_id' => 'You do not have access to this site.']);
            }
        }

        // The site the record will end up on, which is what the resident must belong to.
        $targetSiteId = $request->filled('site_id') ? $request->site_id : $formData->site_id;

        $request->validate([
            'employee_type' => ['required', 'string'],
            'mcls_name' => ['nullable', 'string'],
            'agency_employee_name' => ['nullable', 'string'],
            'log_date' => ['required', 'date'],
            'shift' => ['required', 'string'],
            // resident_id was written straight through with no rule at all.
            'resident_id' => ['nullable', Rule::exists('residents', 'id')->where('site_id', $targetSiteId)],
            'medical' => ['nullable', 'string'],
            'behavior' => ['nullable', 'string'],
            'activities' => ['nullable', 'string'],
            'nutrition' => ['nullable', 'string'],
            'sleep' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'temperature' => ['nullable', 'numeric'],
        ]);

        $formData->update($request->only([
            'site_id',
            'resident_id',
            'employee_type',
            'mcls_name',
            'agency_employee_name',
            'log_date',
            'shift',
            'medical',
            'behavior',
            'activities',
            'nutrition',
            'sleep',
            'notes',
            'temperature',
        ]));

        return redirect()->back()->with('success', 'Log data updated successfully.');
    }

    public function residentform()
    {
        $site = DB::table('site_users')->where('user_id', Auth::id())->first();
        if (!$site) {
            abort(403, 'Your account is not assigned to a site, so the site log form is unavailable.');
        }

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
            ->whereBetween('log_date_time', [$startOfWeek->copy()->startOfDay(), $endOfWeek->copy()->endOfDay()])
            ->get();

        $prev_sat_data = DB::table('site_checklist_data as s')
            ->join('xwalk_site_checklist_type as x', 's.site_checklist_id', '=', 'x.id')
            ->where('s.site_id', $site->site_id)
            ->where('s.sat_bool', 1)
            ->where('checklist_type', 'NIGHT SHIFT CHECKLIST')
            ->whereBetween('s.log_date_time', [$prevSat->copy()->startOfDay(), $prevSat->copy()->endOfDay()])
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

    /** Rows per page, capped so a hand-edited query string cannot ask for everything. */
    private function perPage(Request $request): int
    {
        return (int) min(max((int) $request->input('per_page', 15), 10), 100);
    }

    /**
     * Build the log query for the current user, applying the same access scoping and
     * filters both log screens use. Shared with the CSV export so the export can never
     * return more than the screen would.
     */
    private function scopedLogQuery(Request $request)
    {
        $accessibleSiteIds = Auth::user()->getAccessibleSites()->pluck('id');
        $query = FormData::whereIn('site_id', $accessibleSiteIds);

        // The two screens use different names for the same date filters.
        $from = $request->input('from_date', $request->input('start_date'));
        $to   = $request->input('to_date', $request->input('end_date'));

        if ($from) { $query->whereDate('log_date', '>=', $from); }
        if ($to)   { $query->whereDate('log_date', '<=', $to); }

        if ($request->filled('site_id') && $accessibleSiteIds->contains($request->site_id)) {
            $query->where('site_id', $request->site_id);
        }
        if ($request->filled('resident_id')) { $query->where('resident_id', $request->resident_id); }
        if ($request->filled('shift'))       { $query->where('shift', $request->shift); }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('mcls_name', 'like', "%$search%")
                  ->orWhere('agency_employee_name', 'like', "%$search%")
                  ->orWhere('agency_name', 'like', "%$search%");
            });
        }

        return $query;
    }

    /**
     * Stream the filtered logs as CSV. Streaming keeps memory flat however many rows
     * match, and this exports the whole filtered set rather than just the page on screen,
     * which the old DOM-scraping export could not do once pagination existed.
     */
    public function export(Request $request)
    {
        $request->validate([
            'from_date'  => ['nullable', 'date'],
            'to_date'    => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ]);

        $query = $this->scopedLogQuery($request)
            ->with(['site:id,name', 'resident:id,name', 'createdBy:id,name'])
            ->orderByDesc('log_date')->orderByDesc('id');

        $filename = 'resident-logs-' . now()->format('Y-m-d-His') . '.csv';

        return response()->stream(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM so Excel reads UTF-8

            fputcsv($out, [
                'Date', 'Time', 'Site', 'Resident', 'Shift', 'Staff Type', 'Staff Name',
                'Agency', 'Temperature', 'ADLs', 'Medical', 'Behaviour', 'Activities',
                'Nutrition', 'Sleep', 'Notes', 'Recorded By',
            ]);

            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->log_date, $r->log_time,
                        $r->site->name ?? '', $r->resident->name ?? '', $r->shift,
                        $r->employee_type === 'mcls' ? 'MCLS' : 'Agency',
                        $r->employee_type === 'mcls' ? $r->mcls_name : $r->agency_employee_name,
                        $r->agency_name, $r->temperature,
                        $r->adls, $r->medical, $r->behavior, $r->activities,
                        $r->nutrition, $r->sleep, $r->notes,
                        $r->createdBy->name ?? '',
                    ]);
                }
            });

            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }

    public function destroy($id)
    {
        $form_data_to_delete = Auth::user()->getAccessibleFormData()->where('id', $id)->firstOrFail();
        $form_data_to_delete->delete();
        return response()->json(['message' => 'Form Record deleted successfully']);
    }
}
