<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || $user->email_verified_at) {
            return response()->json(['message' => 'This verification request is invalid.'], 422);
        }

        if (! $user->email_verification_code
            || ! $user->email_verification_expires_at
            || now()->greaterThan($user->email_verification_expires_at)
            || ! Hash::check($data['code'], $user->email_verification_code)) {
            return response()->json(['message' => 'Invalid or expired verification code.'], 422);
        }

        $user->update([
            'email_verified_at' => now(),
            'email_verification_code' => null,
            'email_verification_expires_at' => null,
        ]);

        Mail::to($user->email)->send(new WelcomeMail($user));

        return response()->json(['message' => 'Email verified successfully.']);
    }

    public function resend(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', $data['email'])->first();

        if (! $user || $user->email_verified_at) {
            return response()->json(['message' => 'If the account exists, a verification code has been sent.']);
        }

        $code = (string) random_int(100000, 999999);
        $user->update([
            'email_verification_code' => Hash::make($code),
            'email_verification_expires_at' => now()->addMinutes(10),
        ]);
        Mail::to($user->email)->send(new VerificationCodeMail($user, $code));

        return response()->json(['message' => 'A new verification code has been sent.']);
    }
}
