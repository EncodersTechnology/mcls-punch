<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormData;
use App\Models\Resident;
use App\Models\Site;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FormDataController extends Controller
{

    public function index()
    {
        // return latest form data
        $form_data = FormData::with('site')->latest()->first();
        $sites = Site::all();
        $residents = Resident::all();
        return view('admin.dashboard', ['sites' => $sites, 'residents' => $residents, 'form_data' => $form_data]);
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

                'site_id' => ['required', 'exists:sites,id'],
                'resident_id' => ['required', 'exists:residents,id'],

                'shift' => ['required', Rule::in(['morning', 'night'])],
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
            $validated['log_time'] = date("H:i:s", strtotime($validated['log_time']));

            $form_data = FormData::create($validated);
            $form_data->load('site');
            return response()->json(['status' => true, 'data' => $form_data], 201);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Internal Server Error. ' . $e->getMessage()], 500);
        }
    }

    public function list()
    {
        $datas = FormData::all();
        return view('admin.log', ['datas' => $datas]);
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
