<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();
        if (!$user || !$user->role || !in_array($user->role->id_role_code, $roles)) {
            return response()->json(['message' => 'Akses ditolak. Role tidak memadai.'], 403);
        }
        return $next($request);
    }
}
