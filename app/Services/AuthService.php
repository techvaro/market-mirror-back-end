<?php

namespace App\Services;

use App\Mail\VerificationCodeMail;
use App\Models\Market;
use App\Models\Notification;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register(array $data): array
    {
        $data['password'] = Hash::make($data['password']);
        $data['role'] = $data['role'] ?? 'buyer';
        $code = (string) random_int(100000, 999999);
        $data['email_verification_code'] = Hash::make($code);
        $data['email_verification_expires_at'] = now()->addMinutes(10);

        $isVendor = $data['role'] === 'vendor';
        if ($isVendor) {
            $data['status'] = 'pending';
        }

        $user = User::create($data);
        Mail::to($user->email)->send(new VerificationCodeMail($user, $code));

        $token = null;
        $refreshToken = null;
        if (! $isVendor) {
            $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);
            $refreshToken = $this->generateRefreshToken($user);
        } else {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'vendor_onboarding',
                'title' => 'Complete vendor onboarding',
                'message' => 'Your vendor account was created. Complete your onboarding details before submitting your application for review.',
                'read' => false,
                'link' => '/vendor-onboarding',
            ]);
        }

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'refresh_token' => $refreshToken,
            'refresh_token_expires_in' => 30 * 24 * 60 * 60,
        ];
    }

    public function login(string $email, string $password): array
    {
        try {
            if (! $token = JWTAuth::attempt(['email' => $email, 'password' => $password])) {
                throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
            }
        } catch (JWTException $e) {
            throw ValidationException::withMessages(['email' => ['Could not create token.']]);
        }

        $user = JWTAuth::user();
        if (! $user->email_verified_at) {
            $this->discardToken($token);
            throw ValidationException::withMessages(['email' => ['Please verify your email address before signing in.']]);
        }
        if ($user->status === 'pending' && $user->role !== 'vendor') {
            $this->discardToken($token);
            throw ValidationException::withMessages(['email' => ['Your account is pending approval.']]);
        }
        $canResubmitVendorApplication = $user->role === 'vendor'
            && $user->vendorApplication?->status === 'Rejected';

        if ($user->status === 'suspended' && ! $canResubmitVendorApplication) {
            $this->discardToken($token);
            throw ValidationException::withMessages(['email' => ['Your account has been suspended.']]);
        }

        if ($user->role === 'mapper') {
            $user->mapperProfile?->update(['last_login_at' => now()]);
        }

        return $this->tokenResponse($user, $token);
    }

    public function refreshFromRefreshToken(string $refreshToken): array
    {
        try {
            $payload = JWTAuth::setToken($refreshToken)->getPayload();
            if ($payload->get('type') !== 'refresh') {
                throw ValidationException::withMessages(['token' => ['Invalid refresh token.']]);
            }
            $user = User::findOrFail($payload->get('sub'));
            JWTAuth::invalidate($refreshToken);

            return $this->tokenResponse($user, JWTAuth::claims(['role' => $user->role])->fromUser($user));
        } catch (TokenInvalidException $e) {
            throw ValidationException::withMessages(['token' => ['Refresh token is invalid or expired.']]);
        }
    }

    private function tokenResponse(User $user, string $token): array
    {
        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'refresh_token' => $this->generateRefreshToken($user),
            'refresh_token_expires_in' => 30 * 24 * 60 * 60,
        ];
    }

    public function issueTokens(User $user): array
    {
        return [
            'user' => $user,
            'token' => JWTAuth::claims(['role' => $user->role])->fromUser($user),
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'refresh_token' => $this->generateRefreshToken($user),
            'refresh_token_expires_in' => 30 * 24 * 60 * 60,
        ];
    }

    private function generateRefreshToken(User $user): string
    {
        $originalTtl = config('jwt.ttl');
        config(['jwt.ttl' => 30 * 24 * 60]);
        $token = JWTAuth::claims(['role' => $user->role, 'type' => 'refresh'])->fromUser($user);
        config(['jwt.ttl' => $originalTtl]);

        return $token;
    }

    public function logout(): bool
    {
        JWTAuth::invalidate(JWTAuth::parseToken());

        return true;
    }

    private function discardToken(string $token): void
    {
        try {
            JWTAuth::setToken($token)->invalidate();
        } catch (\Throwable) {
        }
    }

    public function me(): User
    {
        $user = JWTAuth::user();
        $user->load(['mapperProfile', 'shops', 'activeMarket', 'vendorApplication']);

        if ($user->role === 'vendor' && $user->vendorApplication?->status === 'Approved' && $user->shops->isEmpty()) {
            $application = $user->vendorApplication;
            $market = Market::where('name', $application->market)->first();

            Shop::create([
                'user_id' => $user->id,
                'market_id' => $market?->id,
                'name' => $application->shop_name,
                'category' => $application->category,
                'description' => $application->description ?? '',
                'location' => trim(implode(', ', array_filter([
                    $application->building_name,
                    $application->shop_address,
                ]))),
                'market' => $application->market,
                'city' => $market?->city ?? $application->market,
                'phone' => $application->phone,
                'shop_number' => $application->shop_number,
            ]);

            $user->load('shops');
        }

        return $user;
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages(['current_password' => ['The current password is incorrect.']]);
        }

        $user->update([
            'password' => Hash::make($newPassword),
            'must_change_password' => false,
        ]);
    }
}
