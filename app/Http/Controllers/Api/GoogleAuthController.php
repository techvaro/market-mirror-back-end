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
        if ($request->filled('redirect_uri')) {
            $request->session()->put('google_redirect_uri', $request->query('redirect_uri'));
        }
        if ($request->filled('state')) {
            // allow passing extra state, but keep role
            $request->session()->put('google_state_extra', $request->query('state'));
        }

        $state = $role;
        if ($request->filled('redirect_uri')) {
            $state = $role.'|'.base64_encode(urlencode($request->query('redirect_uri')));
        } elseif ($request->session()->has('google_redirect_uri')) {
            $state = $role.'|'.base64_encode(urlencode($request->session()->get('google_redirect_uri')));
        }

        return Socialite::driver('google')
            ->stateless()
            ->with(['state' => $state])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $stateParam = $request->query('state');
        $role = $request->session()->pull('google_role', 'buyer');
        $customRedirect = $request->session()->pull('google_redirect_uri');

        if (is_string($stateParam) && str_contains($stateParam, '|')) {
            [$roleFromState, $encodedRedirect] = explode('|', $stateParam, 2);
            if (in_array($roleFromState, ['buyer', 'vendor'], true)) {
                $role = $roleFromState;
            }
            if ($encodedRedirect) {
                $customRedirect = urldecode(base64_decode($encodedRedirect));
            }
        } elseif (in_array($stateParam, ['buyer', 'vendor'], true)) {
            $role = $stateParam;
        }

        if (! in_array($role, ['buyer', 'vendor'], true)) {
            $role = 'buyer';
        }

        $frontend = $customRedirect ?: ($role === 'vendor'
            ? config('services.google.vendor_frontend')
            : config('services.google.buyer_frontend'));

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
                if (str_starts_with($frontend, 'http://') || str_starts_with($frontend, 'https://')) {
                    return redirect()->to($frontend.'/auth/google/success?error=suspended');
                }
                $sep = str_contains($frontend, '?') ? '&' : '?';
                return redirect()->to($frontend.$sep.'error=suspended');
            }

            $tokens = $this->authService->issueTokens($user);

            if (str_starts_with($frontend, 'http://') || str_starts_with($frontend, 'https://')) {
                return redirect()->to($frontend.'/auth/google/success?'.http_build_query([
                    'token' => $tokens['token'],
                    'refresh_token' => $tokens['refresh_token'],
                ]));
            }

            $sep = str_contains($frontend, '?') ? '&' : '?';
            return redirect()->to($frontend.$sep.http_build_query([
                'token' => $tokens['token'],
                'refresh_token' => $tokens['refresh_token'],
            ]));
        } catch (\Throwable $exception) {
            report($exception);

            if (isset($frontend) && (str_starts_with($frontend, 'http://') || str_starts_with($frontend, 'https://'))) {
                return redirect()->to($frontend.'/auth/google/success?error=oauth_failed');
            } elseif (isset($frontend)) {
                $sep = str_contains($frontend, '?') ? '&' : '?';
                return redirect()->to($frontend.$sep.'error=oauth_failed');
            }

            return redirect()->to(config('app.url').'/auth/google/success?error=oauth_failed');
        }
    }
}
