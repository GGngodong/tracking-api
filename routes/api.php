<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermitLetterController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiPermitLetterMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

//Route::post('/users', [\App\Http\Controllers\UserController::class, 'register']);
//Route::post('/users/login', [\App\Http\Controllers\UserController::class, 'login']);
//Route::middleware(\App\Http\Middleware\ApiAuthMiddleware::class)->group(function () {
//    Route::get('/users/current', [\App\Http\Controllers\UserController::class, 'getUser']);
//    Route::patch('/users/current', [\App\Http\Controllers\UserController::class, 'update']);
//    Route::delete('/users/logout',[\App\Http\Controllers\UserController::class, 'logout']);
//});
//Dev Route

// USERS
Route::post('/users', [UserController::class, 'register']);
Route::post('/users/login', [UserController::class, 'login']);
Route::post('/users/forgot-password', [UserController::class, 'sendPasswordResetEmail']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/check-email-verified', [UserController::class, 'checkEmailVerified']);
    Route::post('/users/send-email-verification', [UserController::class, 'sendEmailVerification']);
    Route::get('/users/current', [UserController::class, 'getUser']);
    Route::patch('/users/current', [UserController::class, 'update']);
    Route::patch('/users/update-token', [UserController::class, 'updateDeviceToken']);
    Route::delete('/users/logout', [UserController::class, 'logout']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::get('/notifications/{id}', [NotificationController::class, 'detailNotification']);
    Route::delete('/notifications/delete/{id}', [NotificationController::class, 'deleteNotification']);
    // PERMIT LETTERS
    Route::middleware(ApiPermitLetterMiddleware::class)->group(function () {
        // POST
        Route::post('/permit-letters/upload', [PermitLetterController::class, 'postPermitLetter']);
        // GET
        Route::get('/permit-letters/{id}', [PermitLetterController::class, 'getPermitLetterById'])->where('id', '[0-9]+');
        Route::get('/permit-letters/', [PermitLetterController::class, 'getAllPermitLetter']);
        Route::get('/permit-letters/rejected', [PermitLetterController::class, 'getRejectedPermitLetter']);
        Route::get('/permit-letters/approved', [PermitLetterController::class, 'getApprovedPermitLetter']);
        Route::get('/permit-letters/approved/{id}', [PermitLetterController::class, 'getApprovedPermitLetterById'])->where('id', '[0-9]+');
        Route::get('/permit-letters/latest', [PermitLetterController::class, 'getLatestPermitLetter']);
        Route::get('/permit-letters/search', [PermitLetterController::class, 'searchPermitLetter']);
        Route::get('/permit-letters/pending', [PermitLetterController::class, 'getPendingPermitLetter']);
        // PATCH
        Route::patch('/permit-letters/edit/{id}', [PermitLetterController::class, 'updatePermitLetter']);
        // DELETE
        Route::delete('/permit-letters/delete/{id}', [PermitLetterController::class, 'deletePermitLetter']);
    });
});

