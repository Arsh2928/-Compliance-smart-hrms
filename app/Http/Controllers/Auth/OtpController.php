<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function showVerifyForm()
    {
        if (!session()->has('otp_email')) {
            return redirect()->route('login');
        }
        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);

        $email = session('otp_email');
        if (!$email) {
            return redirect()->route('login')->withErrors(['email' => 'Session expired. Please login again.']);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'User not found.']);
        }

        if ((string)$user->otp_code !== (string)$request->otp || now()->greaterThan($user->otp_expires_at)) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        $user->update([
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        session()->forget('otp_email');

        return redirect()->route('login')->with('status', 'Email verified successfully! Please wait for HR/Admin to approve your account before logging in.');
    }

    public function resendOtp(Request $request)
    {
        $email = session('otp_email');
        if (!$email) {
            return redirect()->route('login')->withErrors(['email' => 'Session expired. Please register again.']);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'User not found.']);
        }

        $otp = random_int(100000, 999999);
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        \Illuminate\Support\Facades\Mail::raw("Your new OTP is: $otp. It expires in 10 minutes.", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Verify your email (Resend)');
        });

        return back()->with('status', 'A new verification OTP has been sent to your email.');
    }
}
