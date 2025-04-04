<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\Site;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'name' => 'required|string|max:255',
            'site_id' => 'required|exists:sites,id',
        ]);
    
        Resident::create($validated);
        $sites = Site::all();
        $residents = Resident::all();
        return redirect()->route('admin.resident')->with(['sites' => $sites, 'residents' => $residents, 'success' => 'Resident Created Successfully']);
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
            'name' => 'required|string|max:255',
            'site_id' => 'required|exists:sites,id',
        ]);
    
        $resident->update($request->all());
        $sites = Site::all();
        $residents = Resident::all();
        return redirect()->route('admin.resident')->with(['sites' => $sites, 'residents' => $residents, 'success' => 'Resident Updated Successfully']);
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
        $sites = Site::all();
        $residents = Resident::all();
        return redirect()->route('admin.resident')->with(['sites' => $sites, 'residents' => $residents, 'success' => 'Resident Deleted Successfully']);
  }
}
