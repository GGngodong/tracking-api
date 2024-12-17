<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\JsonResponse;


class UserController extends Controller
{
    public function register(UserRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (User::where('email', $data['email'])->count() == 1) {
            throw new HttpResponseException(response([
                'errors' => [
                    'email' => ['The email has already been taken.', 'Invalid email address.'],
                ]
            ], 400));
        }


        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $user->save();

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function login(UserLoginRequest $request): UserResource
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => ['The email or password is incorrect.'],
                ]
            ], 401));
        }

        $user->token = Str::uuid()->toString();
        $user->save();

        return new UserResource($user);
    }

    public function getUser(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'errors' => [
                    'message' => 'User not found.'
                ]
            ], 404);
        }

        return (new UserResource($user))->response();
    }

    public function update(UserUpdateRequest $request): UserResource{
        $data = $request->validated();
        $user = Auth::user();
        if(isset($data['username'])){
            $user->username = $data['username'];
        }
        if(isset($data['password'])){
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        return new UserResource($user);
    }

    public function logout(Request $request): JsonResponse {
        $user = Auth::user();
        $user->token = null;
        $user->save();
        return response()->json([
            'data' => [
                true
            ]
        ], 204);
    }
}
