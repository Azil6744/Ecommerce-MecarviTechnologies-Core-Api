<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }
        
        // For API routes, return JSON response instead of redirecting
        if ($request->is('api/*')) {
            abort(response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please provide a valid API token.',
            ], 401));
        }
        
        return route('login');
    }
}
