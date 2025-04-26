<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormData;
use App\Models\Resident;
use App\Models\Site;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Auth;

class FormDataController extends Controller
{

    public function index()
    {
        // return latest form data
        $form_data = FormData::with('site')->latest()->first();
        $sites = Site::all();
        $site = DB::table('site_users')->where('user_id', Auth::id())->first();
        if($site){
            $site_residents = DB::table('residents')->where('site_id', $site->site_id)->get();
        }
        else{
            $site_residents = [];
        }

        $residents = Resident::all();
        return view('admin.new-dashboard', ['sites' => $sites, 'site'=>$site,'site_residents'=>$site_residents, 'residents' => $residents, 'form_data' => $form_data]);
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
            ->first();

        return response()->json(['data'=>$form_data]);
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

                'shift' => ['required', Rule::in(['morning', 'night'])],
                'adls' => 'required|string',
                'medical' => 'required|string',
                'behavior' => 'required|string',
                'activities' => 'nullable|string',
                'nutrition' => 'nullable|string',
                'sleep' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);
            $validated['log_date'] = now()->toDateString(); // returns 'YYYY-MM-DD'
            $validated['log_time'] = now()->format('H:i:s'); // returns 'HH:MM:SS'
            $site = DB::table('site_users')->where('user_id', Auth::id())->first();
            $validated['site_id'] = $site->site_id;
            $form_data = FormData::create($validated);
            return response()->json(['status' => true, 'data' => $form_data], 201);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Internal Server Error. ' . $e->getMessage()], 500);
        }
    }

    public function list()
    {
        $site = DB::table('site_users')->where('user_id', Auth::id())->first();
        if($site){
        $datas = FormData::where('site_id', $site->site_id)->get();
        }
        else{
            $datas = [];
        }
        return view('employee.log', ['datas' => $datas]);
    }

    public function adminlog(Request $request)
{
    $site_id = $request->input('site_id');
    $resident_id = $request->input('resident_id');

    $sites = Site::all();
    $residents = Resident::when($site_id, function($query) use ($site_id) {
        $query->where('site_id', $site_id);
    })->get();

    $datas = FormData::query()
        ->when($site_id, function($query) use ($site_id) {
            $query->where('site_id', $site_id);
        })
        ->when($resident_id, function($query) use ($resident_id) {
            $query->where('resident_id', $resident_id);
        })
        ->get();

    return view('admin.log', compact('datas', 'sites', 'residents', 'site_id', 'resident_id'));
}

    public function residentform(){
        $site = DB::table('site_users')->where('user_id', Auth::id())->first();
        $checklistTypes = DB::table('xwalk_site_checklist_type')
                            ->where('is_deleted', 0)
                            ->where('status', 1)
                            ->get()->groupBy('checklist_type');
                            
        $siteChecklistSettings = DB::table('site_checklist_settings as s')
                            ->join('xwalk_site_checklist_type as x', 's.site_checklist_id', '=', 'x.id')
                            ->select(
                                's.*',
                                'x.checklist_type',
                                'x.group_name',
                                'x.task_name'
                            )
                            ->where('s.is_deleted', 0)
                            ->where('s.status', 1)
                            ->where('x.is_deleted', 0)
                            ->where('x.status', 1)
                            ->where('site_id',$site->site_id)
                            ->get();
        return view('employee.logform', [
            'checklistTypes' => $checklistTypes,
            'siteChecklistSettings' => $siteChecklistSettings,
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
