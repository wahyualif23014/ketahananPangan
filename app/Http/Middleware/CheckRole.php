<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Security Optimization: Kick out soft-deleted / deactivated users instantly
        if (isset($user->deletestatus) && $user->deletestatus !== '2') {
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akun Anda telah dinonaktifkan.'], 403);
            }
            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan.');
        }

        if (in_array($request->user()->role, $roles)) {
            return $next($request);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // For web requests: Throw 403 Forbidden instead of redirecting
        abort(403, 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}
