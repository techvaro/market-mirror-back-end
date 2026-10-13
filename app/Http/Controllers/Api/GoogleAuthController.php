<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function redirect(Request $request): RedirectResponse
    {
        $role = $request->query('role', 'buyer');

        Validator::make(['role' => $role], [
            'role' => ['required', 'in:buyer,vendor'],
        ])->validate();

        $request->session()->put('google_role', $role);

        return Socialite::driver('google')
            ->stateless()
            ->with(['state' => $role])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $role = $request->query('state', $request->session()->pull('google_role', 'buyer'));
        if (! in_array($role, ['buyer', 'vendor'], true)) {
            $role = 'buyer';
        }
        $frontend = $role === 'vendor'
            ? config('services.google.vendor_frontend')
            : config('services.google.buyer_frontend');

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $email = $googleUser->getEmail();

            if (! $email) {
                return redirect()->to($frontend.'/auth/google/success?error=no_email');
            }

            $user = User::where('email', $email)->first();

            if ($user && $user->role !== $role) {
                return redirect()->to($frontend.'/auth/google/success?error=role_mismatch');
            }

            $avatar = $googleUser->getAvatar();

            if (! $user) {
                $user = new User;
                $user->forceFill([
                    'name' => $googleUser->getName() ?: Str::before($email, '@'),
                    'email' => $email,
                    'google_id' => $googleUser->getId(),
                    'avatar_url' => $avatar,
                    'profile_image_url' => $avatar,
                    'email_verified_at' => now(),
                    'role' => $role,
                    'status' => $role === 'vendor' ? 'pending' : 'active',
                ])->save();
            } else {
                $user->forceFill([
                    'google_id' => $googleUser->getId(),
                    'avatar_url' => $avatar ?: $user->avatar_url,
                    'profile_image_url' => $user->profile_image_url ?: $avatar,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            }

            $canResubmitVendorApplication = $user
                && $user->role === 'vendor'
                && $user->vendorApplication?->status === 'Rejected';

            if ($user->status === 'suspended' && ! $canResubmitVendorApplication) {
                return redirect()->to($frontend.'/auth/google/success?error=suspended');
            }

            $tokens = $this->authService->issueTokens($user);

            return redirect()->to($frontend.'/auth/google/success?'.http_build_query([
                'token' => $tokens['token'],
                'refresh_token' => $tokens['refresh_token'],
            ]));
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->to($frontend.'/auth/google/success?error=oauth_failed');
        }
    }
}
