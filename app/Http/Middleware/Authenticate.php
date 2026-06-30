<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // For API requests we must not attempt to redirect to a web route.
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        // For web requests, try to resolve the 'login' route but avoid
        // throwing a RouteNotFoundException when it's not defined.
        try {
            return route('login');
        } catch (\Throwable $e) {
            Log::warning('Route [login] not defined: ' . $e->getMessage());
            return null;
        }
    }
}
