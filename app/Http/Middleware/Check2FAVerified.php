<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Check2FAVerified
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        if (!$user->two_fa_verified_at) {
            return response()->json([
                'message' => '2FA belum diverifikasi'
            ], 403);
        }

        return $next($request);
    }
}
