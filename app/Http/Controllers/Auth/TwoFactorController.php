<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function __construct(protected TwoFactorService $twoFactorService) {}

    public function send(Request $request)
    {
        $user = $request->user();
        $this->twoFactorService->generateAndSend($user);
        return response()->json(['message' => 'Kode 2FA telah dikirim ke email Anda.']);
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $user = $request->user();
        $verified = $this->twoFactorService->verify($user, $request->code);

        if (!$verified) {
            return response()->json(['message' => 'Kode tidak valid atau sudah kedaluwarsa.'], 422);
        }

        return response()->json([
            'message'     => 'Verifikasi 2FA berhasil.',
            'user'        => $user->load('role'),
            'is_approved' => (bool) $user->is_approved,
        ]);
    }
}
