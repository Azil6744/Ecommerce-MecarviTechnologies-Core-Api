<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyOwnership
{
    /**
     * Handle an incoming request - Verifies user owns the requested resource.
     * 
     * Usage: 
     * - Route model binding should provide the resource model
     * - The middleware checks if the authenticated user matches the resource's user_id
     * 
     * Example routes:
     * Route::get('/addresses/{address}', [AddressController::class, 'show'])
     *     ->middleware('verify.ownership:address');
     */
    public function handle(Request $request, Closure $next, $paramName = null): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Authentication required',
                'code' => 401,
            ], 401);
        }

        $userId = $request->user()->id;

        // If parameter name is provided, check direct ownership
        if ($paramName && $request->route($paramName)) {
            $resource = $request->route($paramName);
            
            // Check if resource has user_id field and matches current user
            if (isset($resource->user_id) && $resource->user_id != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden: You do not have access to this resource',
                    'code' => 403,
                ], 403);
            }
        }

        return $next($request);
    }
}
