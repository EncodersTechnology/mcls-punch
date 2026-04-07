<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function getResidents(Request $request)
    {
        $site_id = $request->site_id;
        // Fetch the residents based on the site_id
        $residents = Resident::where('site_id', $site_id)->get();
        return $residents;
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('residents')->where(function ($query) use ($request) {
                    return $query->where('site_id', $request->site_id);
                }),
            ],
            'site_id' => 'required|exists:sites,id',
        ], [
            'name.unique' => 'A resident with this name already exists at the selected site.',
        ]);

        Resident::create($validated);
        return redirect()->to(route('admin.resident') . '#residents-tab')->with('success', 'Resident Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Resident $resident)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Resident $resident)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Resident $resident)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('residents')->where(function ($query) use ($request) {
                    return $query->where('site_id', $request->site_id);
                })->ignore($resident->id),
            ],
            'site_id' => 'required|exists:sites,id',
        ], [
            'name.unique' => 'A resident with this name already exists at the selected site.',
        ]);

        $resident->update($request->all());
        return redirect()->to(route('admin.resident') . '#residents-tab')->with('success', 'Resident Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Find the resident by ID
        $resident = Resident::findOrFail($id);

        // Delete the resident
        $resident->delete();

        // Redirect back with a success message
        return redirect()->to(route('admin.resident') . '#residents-tab')->with('success', 'Resident Deleted Successfully');
    }
}
