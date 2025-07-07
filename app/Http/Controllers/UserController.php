<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\AuthError;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\Auth\InvalidPassword;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\AuthException;
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
        try {
            $firebaseUser = $this->firebaseAuth->createUser([
                'email' => $data['email'],
                'password' => $data['password'],
                'displayName' => $data['username'],
            ]);

            $user = User::create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'division' => $data['division'] ?? null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully.',
                'data' => new UserResource($user),
            ], 201);
        } catch (EmailExists $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'That email is already registered with Firebase.',
            ], 409);
        } catch (AuthException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Firebase error: ' . $e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $firebaseUser = $this->firebaseAuth->getUserByEmail($credentials['email']);
        } catch (UserNotFound $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email not registered.',
                'data' => null,
            ], 404);
        }

        try {
            $signInResult = $this->firebaseAuth
                ->signInWithEmailAndPassword(
                    $credentials['email'],
                    $credentials['password']
                );
            $firebaseIdToken = $signInResult->idToken();

            $user = User::firstOrCreate(
                ['email' => $firebaseUser->email],
                [
                    'username' => $firebaseUser->displayName,
                    'password' => bcrypt($credentials['password']),
                ]
            );

            $token = $user->createToken('auth_token')->plainTextToken;
            $user->token = $token;

            return response()->json([
                'statusCode' => 200,
                'status' => 'success',
                'message' => 'Login successful.',
                'data' => array_merge(
                    (new UserResource($user))->toArray($request),
                    ['token' => $token]
                ),
            ], 200);

        } catch (InvalidPassword $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid password.',
                'data' => null,
            ], 401);

        } catch (AuthException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication error: ' . $e->getMessage(),
                'data' => null,
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login failed: ' . $e->getMessage(),
                'data' => null,
            ], 500);
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
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated or already logged out.',
                'data' => null,
            ], 401);
        }

        try {
            if ($user->tokens()->count() === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No active session found or already logged out.',
                    'data' => null,
                ], 400);
            }

            $user->tokens()->delete();
            $user->device_token = null;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully.',
                'data' => null,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
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

    public function updateDeviceToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_token' => 'required|string',
        ]);

        $user = $request->user();
        $user->device_token = $data['device_token'];
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Device token updated successfully.',
        ]);
    }

    public function sendPasswordResetEmail(Request $request): JsonResponse
    {
        // 1) Validate input
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');

        // 2) Verify the email is registered in Firebase
        try {
            $this->firebaseAuth->getUserByEmail($email);
        } catch (UserNotFound $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email not found.',
                'data' => null,
            ], 404);
        }

        // 3) Attempt to send the reset link
        try {
            $this->firebaseAuth->sendPasswordResetLink($email);

            return response()->json([
                'status' => 'success',
                'message' => 'Password reset email sent successfully.',
                'data' => null,
            ], 200);

        } catch (AuthException $e) {
            // Firebase-specific errors (e.g. rate‑limit, malformed email)
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send password reset email: ' . $e->getMessage(),
                'data' => null,
            ], 500);

        } catch (Exception $e) {
            // Catch‑all for anything else
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred.',
                'data' => null,
            ], 500);
        }
    }

    public function sendEmailVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $this->firebaseAuth->sendEmailVerificationLink($user->email);
            return response()->json([
                'status' => 'success',
                'message' => 'Email verification link sent successfully.',
            ]);
        } catch (AuthError $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send email verification link.',
            ], 400);
        }
    }

    public function checkEmailVerified(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $firebaseUser = $this->firebaseAuth->getUserByEmail($user->email);
            if ($firebaseUser->emailVerified) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Email is verified.',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email is not verified.',
                ], 400);
            }
        } catch (AuthError $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check email verification status.',
            ], 400);
        }
    }


}
