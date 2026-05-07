<x-mail::message>
# Reset Your Password

You are receiving this email because we received a password reset request for your account.

Here is your 6-digit verification code to reset your password:

<x-mail::panel>
<div style="text-align: center; font-size: 24px; letter-spacing: 4px; font-weight: bold; color: #4F46E5;">
{{ $otpCode }}
</div>
</x-mail::panel>

This code will expire in 15 minutes.

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
