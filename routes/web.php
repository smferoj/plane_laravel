<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Middleware\TokenVerificationMiddleware;
use Illuminate\Support\Facades\Route;

 
Route::post('/user-login', [AuthController::class, 'UserLogin']);
Route::post('/send-otp', [AuthController::class, 'SendOTPCode']);
Route::post('/verify-otp', [AuthController::class, 'VerifyOTP']);
// Token vefification needed
Route::post('/reset-password', [AuthController::class, 'ResetPassword'])->middleware([TokenVerificationMiddleware::class]);