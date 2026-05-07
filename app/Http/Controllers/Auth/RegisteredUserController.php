<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration or account activation.
     *
     * Two paths:
     *  A) User pre-created by admin (has no real password — bcrypt('password123')):
     *     → We allow them to set their own password and complete the account.
     *  B) Brand new user (email not in DB at all):
     *     → Normal registration, role defaults to 'employee'.
     *  C) User already has a real password set (fully registered):
     *     → Block and redirect to login.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::min(8)],
        ]);

        $existingUser = User::where('email', strtolower($request->email))->first();

        if ($existingUser) {
            // Check if they were pre-created by admin (default password123)
            if (Hash::check('password123', $existingUser->password)) {
                // Account activation: let them set their real password
                $existingUser->update([
                    'name'     => $request->name,
                    'phone'    => $request->phone ?? $existingUser->phone,
                    'password' => Hash::make($request->password),
                ]);

                Auth::guard('web')->login($existingUser);
                $request->session()->regenerate();
                return redirect()->route('dashboard');
            }

            // Already fully registered — block
            return back()->withErrors([
                'email' => 'An account with this email already exists. Please log in instead.',
            ])->withInput($request->only('name', 'email', 'phone'));
        }

        // Brand new user
        $otp = random_int(100000, 999999);
        $user = User::create([
            'name'     => $request->name,
            'email'    => strtolower($request->email),
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => 'employee',
            'status'   => 'pending',
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // Send OTP via mail
        \Illuminate\Support\Facades\Mail::raw("Your OTP is: $otp. It expires in 10 minutes.", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Verify your email');
        });

        event(new Registered($user));

        // Instead of logging in, keep them out or log them in with restricted access?
        // If we don't log them in, we must pass the email to the OTP page via session.
        session(['otp_email' => $user->email]);

        return redirect()->route('otp.verify');
    }
}
