<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdminMfaVerified
{
    public function handle(Request $request, Closure $next)
    {
        $centralUser = $request->attributes->get('central_auth_user');

        if (is_array($centralUser)
            && ! empty($centralUser['mfa_required'])
            && empty($centralUser['mfa_verified_at'])
        ) {
            return response()->json([
                'message' => 'Admin MFA setup is required before this action.',
                'next_step' => 'setup_mfa',
            ], 403);
        }

        return $next($request);
    }
}
