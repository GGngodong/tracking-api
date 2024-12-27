<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class ApiPermitLetterMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        Log::info('Middleware User:', ['user' => $user]);

        if (!$user) {
            return response()->json([
                'errors' => ['message' => 'Unauthorized. User not found.']
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->role !== 'ADMIN' && $request->method() != 'GET') {
            return response()->json([
                'errors' => [
                    'message' => 'Unauthorized. You do not have the required permissions to perform this action.',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }

}
