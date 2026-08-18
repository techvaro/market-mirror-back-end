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

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
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

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ];
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
