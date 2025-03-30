<?php
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('defualtregister', [AuthController::class, 'defualtregister']);


// Register for verify link
Route::post('register', [AuthController::class, 'register']);

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return response()->json(['message' => 'Email verified successfully.']);
})->middleware(['signed'])->name('verification.verify');



// Send OTP to the user's email
Route::post('send-otp', [AuthController::class, 'sendOtp']);

// Verify OTP to verify the user's email
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);