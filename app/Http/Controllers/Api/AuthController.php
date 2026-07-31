<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string',
            'role' => 'nullable|in:buyer,vendor',
        ]);

        $data = $this->authService->register($validated);

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Registration successful',
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $data = $this->authService->login($request->email, $request->password);

        return response()->json([
            'success' => true,
            'data' => $data,
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

    public function refresh()
    {
        $data = $this->authService->refresh();

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Token refreshed',
        ]);
    }

    public function me()
    {
        return response()->json([
            'success' => true,
            'data' => $this->authService->me(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string',
            'location' => 'sometimes|nullable|string',
            'profile_image_url' => 'sometimes|nullable|string',
            'avatar_url' => 'sometimes|nullable|string',
        ]);

        $user = $this->authService->updateProfile($request->user(), $validated);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'Profile updated',
        ]);
    }
}
