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

        if (in_array($request->user()->role, $roles)) {
            return $next($request);
        }

        // For AJAX/JSON requests, return 403 JSON
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // For web requests: redirect to own dashboard (prevents route enumeration)
        $home = match ($request->user()->role) {
            'admin'    => route('admin.dashboard'),
            'operator' => route('operator.dashboard'),
            'view'     => route('view.dashboard'),
            default    => route('login'),
        };

        return redirect($home)->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}
