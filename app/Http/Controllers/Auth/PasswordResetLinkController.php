<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user) {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->forceFill([
                'otp_code' => $otp,
                'otp_expires_at' => now()->addMinutes(15),
            ])->save();

            \Illuminate\Support\Facades\Mail::raw("Your Password Reset Code is: $otp. It expires in 15 minutes.", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Password Reset Code');
            });
            session()->put('reset_email', $user->email);
        } else {
            // To prevent email enumeration, we still redirect to the OTP form
            // but we don't actually send an email or set the reset_email in session.
            // Or we can just set it anyway, it will just fail validation later.
            session()->put('reset_email', $request->email);
        }

        return redirect()->route('password.otp.form')
                         ->with('status', 'We have emailed your password reset OTP!');
    }
}
