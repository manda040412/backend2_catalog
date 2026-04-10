<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ApprovalController extends Controller
{
    /**
     * GET /api/admin/approvals?status=pending
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $approvals = Approval::with(['user.role'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        return response()->json($approvals);
    }

    /**
     * PATCH /api/admin/approvals/{id}
     * Body: { status: "approved"|"rejected", notes?: "..." }
     */
    public function process(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes'  => 'nullable|string|max:500',
        ]);

        $approval = Approval::with('user')->findOrFail($id);

        if ($approval->status !== 'pending') {
            return response()->json(['message' => 'Approval ini sudah diproses sebelumnya.'], 409);
        }

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
            'message'  => "User berhasil di-{$request->status}.",
            'approval' => $approval->fresh(['user.role']),
        ]);
    }
}
