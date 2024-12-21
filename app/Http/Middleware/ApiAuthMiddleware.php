<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
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

        $token = str_replace('Bearer ', '', $authHeader);


        $user = User::where('token', $token)->first();

        if ($user) {
            Auth::login($user);
            $request->merge(['role' => $user->role]);
            return $next($request);
        } else {
            return response()->json([
                'errors' => [
                    'message' => 'Unauthorized. Invalid token.',
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
//        $token = $request->header('Authorization');
//        $authenticate = true;
//        if (!$token) {
//            $authenticate = false;
//
//        }
//
//        $user = User::where('token', $token)->first();
//        if (!$user) {
//            $authenticate = false;
//        } else {
//            Auth::login($user);
//        }
//
//
//        if ($authenticate) {
//            return $next($request);
//        } else {
//            return response()->json([
//                'errors' => [
//                    'message' => 'Unauthorized.'
//                ]
//            ])->setStatusCode(Response::HTTP_UNAUTHORIZED);
//        }
}
