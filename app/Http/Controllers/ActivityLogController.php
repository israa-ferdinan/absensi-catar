<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with(['user', 'peserta'])
            ->latest()
            ->paginate(20);

        return view('activity_logs.index', compact('logs'));
    }
}