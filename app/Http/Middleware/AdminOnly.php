<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Authentication required',
                'code' => 401,
            ], 401);
        }

        // Check if user has admin role using Spatie Permission
        if (!$request->user()->hasRole(['super_admin', 'admin', 'editor'])) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Admin access required',
                'code' => 403,
                'user_roles' => $request->user()->roles->pluck('name')->toArray(),
            ], 403);
        }

        return $next($request);
    }
}
