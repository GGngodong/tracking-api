<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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

        if (!$user) {
            return response()->json([
                'errors' => [
                    'message' => 'Unauthorized.'
                ]
            ])->setStatusCode(Response::HTTP_UNAUTHORIZED);
        }

        if ($request->isMethod('post') || $request->isMethod('put')) {
            if ($user->role !== 'admin') {
                return response()->json([
                    'errors' => [
                        'message' => 'Forbidden.'
                    ]
                ])->setStatusCode(Response::HTTP_FORBIDDEN);
            }
        }

        return $next($request);
    }
}
