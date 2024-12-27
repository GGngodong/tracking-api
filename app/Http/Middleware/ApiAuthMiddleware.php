<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'errors' => [
                    'message' => 'Unauthorized. Missing or invalid token format.',
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $tokenSring = str_replace('Bearer ', '', $authHeader);
        $token = PersonalAccessToken::findToken($tokenSring);

        if (!$token || !$token->tokenable) {
            return response()->json([
                'errors' => ['message' => 'Unauthorized. Invalid token.'],
            ], Response::HTTP_UNAUTHORIZED);
        }

        Auth::login($token->tokenable);
        $request->merge(['role' => $token->tokenable->role]);

        return $next($request);
    }

}
