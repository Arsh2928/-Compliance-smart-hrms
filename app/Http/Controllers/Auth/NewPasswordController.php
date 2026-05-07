<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function showOtpForm(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        if (!session('reset_email')) {
            return redirect()->route('password.request');
        }
        return view('auth.reset-password-otp');
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate(['otp' => 'required|string']);

        $email = session('reset_email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        $user = User::where('email', $email)->first();

        if (!$user || $user->otp_code !== $request->otp || now()->greaterThan($user->otp_expires_at)) {
            return back()->with('error', 'The OTP is invalid or has expired.');
        }

        // OTP is valid. Clear it and set verified flag.
        $user->forceFill(['otp_code' => null, 'otp_expires_at' => null])->save();
        session()->put('reset_otp_verified', true);

        return redirect()->route('password.reset')->with('status', 'OTP verified successfully!');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $email = session('reset_email');
        if (!$email) {
            return redirect()->route('password.request')->withErrors(['email' => 'Session expired. Please request a new code.']);
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->forceFill([
                'otp_code' => $otp,
                'otp_expires_at' => now()->addMinutes(15),
            ])->save();

            \Illuminate\Support\Facades\Mail::raw("Your new Password Reset Code is: $otp. It expires in 15 minutes.", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Password Reset Code (Resend)');
            });
        }

        return back()->with('status', 'A new OTP has been sent to your email.');
    }

    /**
     * Display the password reset view.
     */
    public function create(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        if (!session('reset_otp_verified')) {
            return redirect()->route('password.request');
        }
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (!session('reset_otp_verified')) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($request->email !== session('reset_email')) {
            return back()->withErrors(['email' => 'The provided email does not match the verified session.']);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        session()->forget(['reset_email', 'reset_otp_verified']);

        return redirect()->route('login')->with('status', 'Your password has been reset successfully!');
    }
}
