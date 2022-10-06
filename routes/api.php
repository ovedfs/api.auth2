<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;

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

// Password Reset
Route::group(['middleware' => ['guest']], function(){
    Route::get('/forgot-password', [PasswordResetController::class, 'forgotPasswordForm'])
        ->name('password.request');

    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPasswordValidate'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetPasswordForm'])
        ->name('password.reset');

    Route::post('/reset-password', [PasswordResetController::class, 'resetPasswordValidate'])
        ->name('password.update');
});
