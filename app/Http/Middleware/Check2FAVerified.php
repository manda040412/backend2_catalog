<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Check2FAVerified
{
    /**
     * 2FA wajib hanya untuk INT/EXT setelah REGISTER.
     * Endpoint /auth/two-factor/verify dan /auth/two-factor/send dikecualikan
     * (mereka memang diakses sebelum 2FA selesai).
     *
     * Setelah 2FA selesai → CheckApproved middleware yang handle cek approval.
     * Kedua middleware ini TERPISAH tugasnya:
     *   - Check2FAVerified  : apakah sudah verifikasi OTP?
     *   - CheckApproved     : apakah sudah diapprove admin?
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $roleCode = $user->role?->id_role_code;

        // SADM dan ADM: tidak butuh 2FA sama sekali
        if (in_array($roleCode, ['SADM', 'ADM'])) {
            return $next($request);
        }

        // User yang login (bukan register): tidak perlu 2FA
        // Cirinya: sudah approved (hanya user approved yang bisa login)
        if ($user->is_approved) {
            return $next($request);
        }

        // User baru register (belum approved): wajib selesaikan 2FA dulu
        if (!$user->two_fa_verified_at) {
            return response()->json([
                'message' => '2FA belum diverifikasi. Silakan cek email untuk kode verifikasi.',
                'reason'  => '2fa_required',
            ], 403);
        }

        // Sudah 2FA tapi belum approved → lanjut ke CheckApproved middleware
        // yang akan blokir akses ke route yang butuh approval
        return $next($request);
    }
}