<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use Carbon\Carbon;
class AuthController extends Controller
{
    public function defualtregister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Fire the Registered event (this triggers the sending of the verification email)
        return response()->json(['message' => 'User registered. ', 'user' => $user]);
    }
    //////////////////////////////////////////////////////////////////////////////////
    public function sendOtp(Request $request)
    {
        // Validate the email
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Find the user
        $user = User::where('email', $request->email)->first();

        // Generate a 6-digit OTP
        $otp = mt_rand(100000, 999999);

        // Store the OTP and its expiry time (10 minutes)
        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        // Send OTP email
        Mail::send('emails.otp', ['otp' => $otp], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Your OTP for Email Verification');
        });

        return response()->json(['message' => 'OTP sent to your email.']);
    }
    public function verifyOtp(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric|digits:6',
        ]);

        // Find the user
        $user = User::where('email', $request->email)->first();

        // Check if OTP is valid and not expired
        if ($user->otp !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP. Please try again.'], 400);
        }

        if (Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP has expired. Please request a new OTP.'], 400);
        }

        // Mark the email as verified
        $user->email_verified_at = Carbon::now();
        $user->otp = null; // Clear the OTP after verification
        $user->otp_expires_at = null;
        $user->save();

        return response()->json(['message' => 'Email verified successfully.']);
    }






    ///////////////////////////////////////////////////////////////////////////////////////

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Fire the Registered event (this triggers the sending of the verification email)
        event(new Registered($user));

        return response()->json(['message' => 'User registered. Please check your email for verification.']);
    }
}
