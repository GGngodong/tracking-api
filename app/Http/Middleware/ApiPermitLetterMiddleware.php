<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiPermitLetterMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
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

        if ($request->isMethod('GET') || $request->isMethod('POST')) {
            return $next($request);
        }

        if ($user->role !== 'ADMIN') {
            return response()->json([
                'errors' => [
                    'message' => 'Unauthorized. You do not have the required permissions to perform this action.'
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
