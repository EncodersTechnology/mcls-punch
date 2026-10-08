<?php

namespace App\Http\Controllers;

use App\Support\HealthCheck;

/**
 * Operational health screen. Restricted to admins because the checks report server
 * internals -- PHP limits, disk space, database version and record counts.
 */
class HealthController extends Controller
{
    public function __construct(private HealthCheck $health)
    {
    }

    public function index()
    {
        return view('health.index', ['report' => $this->health->run()]);
    }

    public function json()
    {
        $report = $this->health->run();

        return response()->json([
            'status' => $report['status'],
            'totals' => $report['totals'],
            'checked_at' => $report['checked_at']->toIso8601String(),
            'groups' => array_map(fn ($g) => [
                'key' => $g['key'],
                'label' => $g['label'],
                'status' => $g['status'],
                'counts' => $g['counts'],
                'checks' => $g['checks'],
            ], $report['groups']),
        ], $report['status'] === HealthCheck::FAIL ? 503 : 200);
    }
}
