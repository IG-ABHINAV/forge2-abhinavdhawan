<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->organization_id) {
            return response()->json(['error' => 'Tenant scoping failed. No organization assigned.'], 403);
        }

        return $next($request);
    }
}
