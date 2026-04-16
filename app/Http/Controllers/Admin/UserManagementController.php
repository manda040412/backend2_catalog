<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $authUser = $request->user();
        $isSuperAdmin = $authUser->role->id_role_code === 'SADM';

        $users = User::with('role')
            ->when(!$isSuperAdmin, function ($q) {
                // Admin biasa hanya bisa lihat user INT dan EXT
                $q->whereHas('role', fn($r) => $r->whereIn('id_role_code', ['INT', 'EXT']));
            })
            ->when($request->search, fn($q) => $q->where(function($q2) use ($request) {
                $q2->where('name', 'LIKE', "%{$request->search}%")
                   ->orWhere('email', 'LIKE', "%{$request->search}%");
            }))
            ->when($request->role, fn($q) => $q->whereHas('role', fn($r) => $r->where('id_role_code', $request->role)))
            ->when($request->status, function($q) use ($request) {
                if ($request->status === 'active') $q->where('is_approved', 1);
                if ($request->status === 'pending') $q->where('is_approved', 0);
                if ($request->status === 'disabled') $q->where('is_active', 0);
            })
            ->paginate(20);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $authUser     = $request->user();
        $isSuperAdmin = $authUser->role->id_role_code === 'SADM';

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id'  => 'required|exists:roles,id_role',
            'company'  => 'nullable|string|max:255',
            'phone'    => 'nullable|string|max:20',
        ]);

        // Admin biasa tidak bisa buat SADM/ADM
        if (!$isSuperAdmin) {
            $role = Role::findOrFail($data['role_id']);
            if (in_array($role->id_role_code, ['SADM', 'ADM'])) {
                return response()->json(['message' => 'Tidak diizinkan membuat role ini.'], 403);
            }
        }

        $user = User::create([
            ...$data,
            'password'    => Hash::make($data['password']),
            'is_approved' => 1,
            'is_active'   => 1,
        ]);

        return response()->json(['message' => 'User berhasil dibuat.', 'user' => $user->load('role')], 201);
    }

    public function update(Request $request, $id)
    {
        $authUser     = $request->user();
        $isSuperAdmin = $authUser->role->id_role_code === 'SADM';

        $user = User::findOrFail($id);

        // Admin biasa hanya bisa edit INT/EXT
        if (!$isSuperAdmin && in_array($user->role->id_role_code, ['SADM', 'ADM'])) {
            return response()->json(['message' => 'Tidak diizinkan mengedit user ini.'], 403);
        }

        $data = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => "sometimes|email|unique:users,email,{$id},id_user",
            'role_id'  => 'sometimes|exists:roles,id_role',
            'company'  => 'nullable|string|max:255',
            'phone'    => 'nullable|string|max:20',
            'password' => 'sometimes|string|min:8',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return response()->json(['message' => 'User diperbarui.', 'user' => $user->load('role')]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->tokens()->delete();
        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus.']);
    }

    /**
     * PATCH /api/admin/users/{id}/toggle
     * Aktifkan / nonaktifkan akses user yang sudah approved
     */
    public function toggleAccess(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Toggle is_approved (0 = disabled, 1 = active)
        $newStatus = !$user->is_approved;
        $user->update(['is_approved' => $newStatus]);

        // Jika dinonaktifkan, hapus semua token
        if (!$newStatus) {
            $user->tokens()->delete();
        }

        return response()->json([
            'message'     => $newStatus ? 'Akses user diaktifkan.' : 'Akses user dinonaktifkan.',
            'is_approved' => $newStatus,
            'user'        => $user->load('role'),
        ]);
    }
}