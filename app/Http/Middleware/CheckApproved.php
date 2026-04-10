<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApproved
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();
        if (!$user || !$user->is_approved) {
            return response()->json(['message' => 'Akun belum disetujui oleh admin.'], 403);
        }
        return $next($request);
    }
}
