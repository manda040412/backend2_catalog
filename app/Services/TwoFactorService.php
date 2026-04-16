<?php

namespace App\Services;

use App\Mail\TwoFactorMail;
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

        // Kirim email HTML yang bagus
        Mail::to($user->email)->send(new TwoFactorMail($code, $user->name));

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