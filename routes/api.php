<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::group(['middleware' => ["auth:sanctum"]], function(){
    Route::get('profile', [AuthController::class, 'profile'])
        ->middleware('verified');

    Route::get('logout', [AuthController::class, 'logout']);

    // Email verification
    Route::get('email/verify', [AuthController::class, 'verifyEmail'])
        ->name('verification.notice');

    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifySuccess'])
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [AuthController::class, 'resendVerifyEmail'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');
});
