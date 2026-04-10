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
        $users = User::with('role')
            ->when($request->search, fn($q) => $q->where('name', 'LIKE', "%{$request->search}%")
                ->orWhere('email', 'LIKE', "%{$request->search}%"))
            ->paginate(20);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id'  => 'required|exists:roles,id_role',
            'company'  => 'nullable|string|max:255',
            'phone'    => 'nullable|string|max:20',
        ]);

        $user = User::create([
            ...$data,
            'password'    => Hash::make($data['password']),
            'is_approved' => 1,
        ]);

        return response()->json(['message' => 'User berhasil dibuat.', 'user' => $user->load('role')], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name'    => 'sometimes|string|max:255',
            'email'   => "sometimes|email|unique:users,email,{$id},id_user",
            'role_id' => 'sometimes|exists:roles,id_role',
            'company' => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'password'=> 'sometimes|string|min:8',
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
        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus.']);
    }
}
