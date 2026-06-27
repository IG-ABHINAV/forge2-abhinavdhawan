<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check() || !in_array(auth()->user()->role, $roles)) {
            return response()->json(['error' => 'Insufficient permissions.'], 403);
        }

        return $next($request);
    }
}
