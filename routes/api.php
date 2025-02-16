<?php

use Illuminate\Http\Request;
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
Route::post('dev/users', [\App\Http\Controllers\UserController::class, 'register']);
Route::post('/dev/users/login', [\App\Http\Controllers\UserController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dev/users/current', [\App\Http\Controllers\UserController::class, 'getUser']);
    Route::patch('/dev/users/current', [\App\Http\Controllers\UserController::class, 'update']);
    Route::delete('/dev/users/logout', [\App\Http\Controllers\UserController::class, 'logout']);

    // PERMIT LETTERS
    Route::middleware(\App\Http\Middleware\ApiPermitLetterMiddleware::class)->group(function () {
        // POST
        Route::post('dev/permit-letters/upload', [\App\Http\Controllers\PermitLetterController::class, 'postPermitLetter']);
        // GET
        Route::get('dev/permit-letters/{id}', [\App\Http\Controllers\PermitLetterController::class, 'getPermitLetterById'])->where('id', '[0-9]+');
        Route::get('dev/permit-letters/', [\App\Http\Controllers\PermitLetterController::class, 'getAllPermitLetter']);
        Route::get('dev/permit-letters/rejected', [\App\Http\Controllers\PermitLetterController::class, 'getRejectedPermitLetter']);
        Route::get('dev/permit-letters/approved', [\App\Http\Controllers\PermitLetterController::class, 'getApprovedPermitLetter']);
        Route::get('dev/permit-letters/approved/{id}', [\App\Http\Controllers\PermitLetterController::class, 'getApprovedPermitLetterById'])->where('id', '[0-9]+');
        Route::get('dev/permit-letters/latest', [\App\Http\Controllers\PermitLetterController::class, 'getLatestPermitLetter']);
        Route::get('dev/permit-letters/search', [\App\Http\Controllers\PermitLetterController::class, 'searchPermitLetter']);
        Route::get('dev/permit-letters/pending', [\App\Http\Controllers\PermitLetterController::class, 'getPendingPermitLetter']);
        // PATCH
        Route::patch('dev/permit-letters/edit/{id}', [\App\Http\Controllers\PermitLetterController::class, 'updatePermitLetter']);
        // DELETE
        Route::delete('dev/permit-letters/delete/{id}', [\App\Http\Controllers\PermitLetterController::class, 'deletePermitLetter']);
    });
});

