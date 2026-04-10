<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\Role;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(protected TwoFactorService $twoFactorService) {}

    /**
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
            'company'               => 'nullable|string|max:255',
            'phone'                 => 'nullable|string|max:20',
        ]);

        $extRole = Role::where('id_role_code', 'EXT')->firstOrFail();

        $user = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'company'     => $data['company'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'role_id'     => $extRole->id_role,
            'is_approved' => 0,
        ]);

        Approval::create([
            'user_id' => $user->id_user,
            'status'  => 'pending',
        ]);

        // Kirim kode 2FA ke email
        $this->twoFactorService->generateAndSend($user);

        // Token diperlukan agar endpoint /auth/two-factor/* bisa diakses
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil. Cek email untuk kode verifikasi 2FA.',
            'token'   => $token,
            'user'    => $user->load('role'),
        ], 201);
    }

    /**
     * POST /api/auth/login
     * Mengembalikan token agar frontend bisa akses endpoint 2FA.
     * Token belum bisa dipakai untuk endpoint lain sebelum 2FA verified.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Reset 2FA setiap login baru (paksa verifikasi ulang)
        $user->update(['two_fa_verified_at' => null]);
        $user->tokens()->delete(); // hapus token lama

        // Kirim kode 2FA baru
        $this->twoFactorService->generateAndSend($user);

        // Buat token sementara untuk akses endpoint 2FA
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Kode verifikasi 2FA telah dikirim ke email Anda.',
            'token'        => $token,
            'requires_2fa' => true,
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil.']);
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request)
    {
        return response()->json($request->user()->load('role'));
    }
}
