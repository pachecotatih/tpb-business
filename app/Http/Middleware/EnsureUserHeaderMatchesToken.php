<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class EnsureUserHeaderMatchesToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\JsonResponse)  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->hasHeader('user')) {
            return $next($request);
        }

        $authenticatedUser = JWTAuth::parseToken()->authenticate();
        if (!$authenticatedUser) {
            throw new \Exception('Usuário não autenticado.', 401);
        }

        if ($authenticatedUser->uid !== $request->header('user')) {
            throw new \Exception('Usuário não autorizado.', 401);
        }

        return $next($request);
    }
}
