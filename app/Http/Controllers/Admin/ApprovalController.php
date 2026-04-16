<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ApprovalNotificationMail;
use App\Models\Approval;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        $approval->update([
            'status'      => $request->status,
            'approved_by' => $request->user()->id_user,
            'notes'       => $request->notes,
            'approved_at' => Carbon::now(),
        ]);

        if ($request->status === 'approved') {
            $approval->user->update(['is_approved' => 1]);
        }

        // Kirim email notifikasi ke user
        try {
            Mail::to($approval->user->email)->send(
                new ApprovalNotificationMail(
                    userName: $approval->user->name,
                    status:   $request->status,
                    notes:    $request->notes,
                )
            );
        } catch (\Exception $e) {
            // Jangan gagalkan request jika email error — cukup log saja
            Log::error('Approval notification email failed: ' . $e->getMessage(), [
                'user_id'     => $approval->user->id_user,
                'approval_id' => $id,
            ]);
        }

        return response()->json([
            'message'  => "User berhasil di-{$request->status}.",
            'approval' => $approval->fresh(['user.role']),
        ]);
    }
}