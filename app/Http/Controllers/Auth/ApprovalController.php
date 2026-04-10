<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ApprovalController extends Controller
{
    public function index()
    {
        $approvals = Approval::with(['user.role'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return response()->json($approvals);
    }

    public function process(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes'  => 'nullable|string|max:500',
        ]);

        $approval = Approval::findOrFail($id);
        $approval->update([
            'status'      => $request->status,
            'approved_by' => $request->user()->id_user,
            'notes'       => $request->notes,
            'approved_at' => Carbon::now(),
        ]);

        if ($request->status === 'approved') {
            $approval->user->update(['is_approved' => 1]);
        }

        return response()->json([
            'message'  => 'Status approval berhasil diperbarui.',
            'approval' => $approval->load('user'),
        ]);
    }
}
