<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->action,  fn($q) => $q->where('action', $request->action))
            ->when($request->date,    fn($q) => $q->whereDate('created_at', $request->date))
            ->when($request->search,  fn($q) => $q->where(function($q2) use ($request) {
                $q2->whereHas('user', fn($u) => $u->where('name', 'LIKE', "%{$request->search}%"))
                   ->orWhere('description', 'LIKE', "%{$request->search}%");
            }))
            ->latest()
            ->paginate(50);

        return response()->json($logs);
    }
}