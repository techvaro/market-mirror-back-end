<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthService
{
    public function register(array $data): array
    {
        $data['password'] = Hash::make($data['password']);
        $data['role'] = $data['role'] ?? 'buyer';

        $user = User::create($data);
        $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);

        $refreshToken = $this->generateRefreshToken($user);

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
        $credentials = ['email' => $email, 'password' => $password];

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }
        } catch (JWTException $e) {
            throw ValidationException::withMessages([
                'email' => ['Could not create token.'],
            ]);
        }

        $user = JWTAuth::user();

        if ($user->status === 'suspended') {
            JWTAuth::invalidate($token);
            throw ValidationException::withMessages([
                'email' => ['Your account has been suspended.'],
            ]);
        }

        $refreshToken = $this->generateRefreshToken($user);

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'refresh_token' => $refreshToken,
            'refresh_token_expires_in' => 30 * 24 * 60 * 60,
        ];
    }

    public function refreshFromRefreshToken(string $refreshToken): array
    {
        try {
            $payload = JWTAuth::setToken($refreshToken)->getPayload();

            if ($payload->get('type') !== 'refresh') {
                throw ValidationException::withMessages([
                    'token' => ['Invalid refresh token.'],
                ]);
            }

            $userId = $payload->get('sub');
            $user = User::findOrFail($userId);

            JWTAuth::invalidate($refreshToken);

            $newAccessToken = JWTAuth::claims(['role' => $user->role])->fromUser($user);
            $newRefreshToken = $this->generateRefreshToken($user);

            return [
                'user' => $user,
                'token' => $newAccessToken,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'refresh_token' => $newRefreshToken,
                'refresh_token_expires_in' => 30 * 24 * 60 * 60,
            ];
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            throw ValidationException::withMessages([
                'token' => ['Refresh token is invalid or expired.'],
            ]);
        }
    }

    private function generateRefreshToken(User $user): string
    {
        $originalTtl = config('jwt.ttl');
        config(['jwt.ttl' => 30 * 24 * 60]); // 30 days

        $refreshToken = JWTAuth::claims([
            'role' => $user->role,
            'type' => 'refresh',
        ])->fromUser($user);

        config(['jwt.ttl' => $originalTtl]); // restore

        return $refreshToken;
    }

    public function logout(): bool
    {
        JWTAuth::invalidate(JWTAuth::parseToken());
        return true;
    }

    public function refresh(): array
    {
        $token = JWTAuth::parseToken()->refresh();

        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }

    public function me(): User
    {
        $user = JWTAuth::user();
        $user->load(['mapperProfile', 'shops', 'activeMarket']);
        return $user;
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }
}
