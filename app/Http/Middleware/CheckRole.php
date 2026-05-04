<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Allow admin to bypass role check or if their role is in the allowed list
        if ($request->user()->role === 'admin' || in_array($request->user()->role, $roles)) {
            return $next($request);
        }

        // For AJAX/JSON requests, return 403 JSON
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // For web requests: Throw 403 Forbidden instead of redirecting
        abort(403, 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}
