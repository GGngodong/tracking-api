<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\AuthError;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Factory;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{
    protected FirebaseAuth $firebaseAuth;

    public function __construct()
    {
        $path = base_path(config('services.firebase.credentials'));
        $firebase = (new Factory)->withServiceAccount($path);
        $this->firebaseAuth = $firebase->createAuth();
    }

    public function register(UserRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            $this->firebaseAuth->getUserByEmail($data['email']);
            throw new HttpResponseException(response([
                'errors' => [
                    'email' => ['The email has already been taken.', 'Invalid email address.'],
                ]
            ], 400));
        } catch (UserNotFound $e) {
        }
        $firebaseUser = $this->firebaseAuth->createUser([
            'email' => $data['email'],
            'password' => $data['password'],
            'displayName' => $data['username'],
        ]);
        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $user->save();

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $signInResult = $this->firebaseAuth->signInWithEmailAndPassword($credentials['email'], $credentials['password']);
            $firebaseIdToken = $signInResult->idToken();
            $firebaseUser = $this->firebaseAuth->getUserByEmail($credentials['email']);
            $user = User::firstOrCreate(
                [
                    'email' => $firebaseUser->email
                ],
                [
                    'username' => $firebaseUser->displayName, 'password' => bcrypt($credentials['password'])
                ]
            );
            $token = $user->createToken('auth_token')->plainTextToken;
            $user->token = $token;
            return response()->json([
                'statusCode' => 200,
                'data' => array_merge(
                    (new UserResource($user))->toArray($request),
                    ['token' => $token]),
                'status' => 'success',
                'message' => 'Login Successful'
            ]);
        } catch (AuthError $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Credentials',
                'data' => null
            ], 401);
        }
    }

    public function getUser(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'statusCode' => 401,
                'status' => 'error',
                'message' => 'User not found.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'data' => new UserResource($user),
            'status' => 'success',
            'message' => 'User found.'
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully.'
        ]);
    }

    public function update(UserUpdateRequest $request): UserResource
    {
        $data = $request->validated();
        $user = Auth::user();
        if (isset($data['username'])) {
            $user->username = $data['username'];
        }
        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        return new UserResource($user);
    }

}
