<?php

namespace App\Http\Controllers;

use App\Models\SiteChecklist;
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
        $site = DB::table('site_users')->where('user_id', Auth::id())->first();
        
        $site_id = $site->site_id;
    
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

        return view('admin.site', [
            'day_shift_checklist' => $day_shift_checklist,
            'night_shift_checklist' => $night_shift_checklist,
        ]);
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
        //
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
