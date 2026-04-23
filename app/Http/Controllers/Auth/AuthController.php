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
     *
     * Semua user baru (INT/EXT):
     *  - is_approved = 0, perlu approval admin
     *  - Wajib 2FA verifikasi email setelah register
     *
     * SADM/ADM tidak bisa self-register — dibuat manual oleh SADM.
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

        $emailDomain = strtolower(substr(strrchr($data['email'], '@'), 1));
        $roleCode    = ($emailDomain === 'tranugerah.com') ? 'INT' : 'EXT';

        $role = Role::where('id_role_code', $roleCode)->firstOrFail();

        $user = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'company'     => $data['company'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'role_id'     => $role->id_role,
            'is_approved' => 0,
        ]);

        Approval::create([
            'user_id' => $user->id_user,
            'status'  => 'pending',
        ]);

        // Token untuk akses endpoint 2FA
        $token = $user->createToken('auth_token')->plainTextToken;

        $mailSent = true;
        try {
            $this->twoFactorService->generateAndSend($user);
        } catch (\Exception $e) {
            $mailSent = false;
            \Log::error('2FA mail failed on register: ' . $e->getMessage());
        }

        return response()->json([
            'message'      => $mailSent
                ? 'Registrasi berhasil. Cek email untuk kode 2FA. Akun aktif setelah disetujui admin.'
                : 'Registrasi berhasil. Kode 2FA gagal dikirim — gunakan tombol Resend.',
            'token'        => $token,
            'user'         => $user->load('role'),
            'requires_2fa' => true,
            'mail_sent'    => $mailSent,
        ], 201);
    }

    /**
     * POST /api/auth/login
     *
     * Aturan login:
     *  - SADM / ADM → langsung masuk, tidak perlu 2FA, tidak perlu approval
     *  - INT  / EXT → cek approved → langsung masuk (tidak ada 2FA saat login)
     *
     * 2FA HANYA di proses register, bukan login.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->with('role')->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $roleCode = $user->role?->id_role_code;
        $isAdmin  = in_array($roleCode, ['SADM', 'ADM']);

        // ── SADM / ADM: langsung masuk ──────────────────────────
        if ($isAdmin) {
            // Auto-fix jika admin belum approved
            if (!$user->is_approved) {
                $user->update(['is_approved' => 1]);
                $user->is_approved = 1;
            }

            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message'      => 'Login berhasil.',
                'token'        => $token,
                'user'         => $user->load('role'),
                'requires_2fa' => false,
            ]);
        }

        // ── INT / EXT: cek approved, lalu langsung masuk ────────
        if (!$user->is_approved) {
            return response()->json([
                'message' => 'Akun Anda belum disetujui oleh admin. Silakan tunggu konfirmasi.',
                'reason'  => 'not_approved',
            ], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Login berhasil.',
            'token'        => $token,
            'user'         => $user->load('role'),
            'requires_2fa' => false,
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