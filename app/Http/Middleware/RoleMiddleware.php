<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Authentication required',
                'code' => 401,
            ], 401);
        }

        // Check if user has any of the required roles
        if (!$request->user()->hasAnyRole($roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Required role not found',
                'code' => 403,
                'required_roles' => $roles,
                'user_roles' => $request->user()->roles->pluck('name')->toArray(),
            ], 403);
        }

        return $next($request);
    }
}