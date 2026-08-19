<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Requests\Api\UploadAvatarRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\UploadService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly UploadService $uploadService
    ) {}

    public function register(RegisterRequest $request)
    {
        $data = $this->authService->register($request->sanitized());

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($data['user']->load('shops')),
                'token' => $data['token'],
                'token_type' => $data['token_type'],
                'expires_in' => $data['expires_in'],
                'refresh_token' => $data['refresh_token'],
                'refresh_token_expires_in' => $data['refresh_token_expires_in'],
            ],
            'message' => 'Registration successful',
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $this->authService->login($request->email, $request->password);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($data['user']->load('shops', 'mapperProfile')),
                'token' => $data['token'],
                'token_type' => $data['token_type'],
                'expires_in' => $data['expires_in'],
                'refresh_token' => $data['refresh_token'],
                'refresh_token_expires_in' => $data['refresh_token_expires_in'],
            ],
            'message' => 'Login successful',
        ]);
    }

    public function logout()
    {
        $this->authService->logout();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function refreshFromRefreshToken(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $data = $this->authService->refreshFromRefreshToken($request->refresh_token);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($data['user']->load('shops', 'mapperProfile')),
                'token' => $data['token'],
                'token_type' => $data['token_type'],
                'expires_in' => $data['expires_in'],
                'refresh_token' => $data['refresh_token'],
                'refresh_token_expires_in' => $data['refresh_token_expires_in'],
            ],
            'message' => 'Token refreshed',
        ]);
    }

    public function me()
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($this->authService->me()),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $this->authService->updateProfile($request->user(), $request->sanitized());

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
            'message' => 'Profile updated',
        ]);
    }

    public function uploadAvatar(UploadAvatarRequest $request)
    {
        $user = $request->user();
        $url = $this->uploadService->store($request->file('avatar'), 'avatars');

        $this->uploadService->deleteByUrl($user->avatar_url);

        $user = $this->authService->updateProfile($user, [
            'avatar_url' => $url,
            'profile_image_url' => $url,
        ]);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
            'message' => 'Avatar updated',
        ]);
    }
}
