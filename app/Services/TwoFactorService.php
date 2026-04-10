<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class TwoFactorService
{
    public function generateAndSend(User $user): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'two_fa_code'        => $code,
            'two_fa_expires_at'  => Carbon::now()->addMinutes(10),
            'two_fa_verified_at' => null,
        ]);

        // Send email — ganti dengan Mail::to($user)->send(new TwoFactorMail($code)) jika mau pakai Mailable
        Mail::raw(
            "Kode verifikasi Anda adalah: {$code}\n\nKode berlaku selama 10 menit.",
            function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('[Timur Raya Catalog] Kode Verifikasi 2FA');
            }
        );

        return $code;
    }

    public function verify(User $user, string $code): bool
    {
        if (
            $user->two_fa_code === $code &&
            $user->two_fa_expires_at &&
            Carbon::now()->lessThan($user->two_fa_expires_at)
        ) {
            $user->update([
                'two_fa_code'        => null,
                'two_fa_expires_at'  => null,
                'two_fa_verified_at' => Carbon::now(),
            ]);
            return true;
        }
        return false;
    }
}
