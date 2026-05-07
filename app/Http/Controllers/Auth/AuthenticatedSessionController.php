<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // 1. Check if email is verified
        if (!$user->email_verified_at) {
            $otp = random_int(100000, 999999);
            $user->update([
                'otp_code' => $otp,
                'otp_expires_at' => now()->addMinutes(10),
            ]);

            \Illuminate\Support\Facades\Mail::raw("Your new OTP is: $otp. It expires in 10 minutes.", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Verify your email');
            });

            session(['otp_email' => $user->email]);
            Auth::logout();
            
            return redirect()->route('otp.verify')->with('status', 'Please verify your email to proceed. A new OTP has been sent.');
        }

        // 2. Check if account is pending approval
        if ($user->status === 'pending') {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account is pending HR/Admin approval.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
