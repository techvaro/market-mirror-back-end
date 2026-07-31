<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->userService->list(
                $request->only(['role', 'status', 'search']),
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->userService->find($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'sometimes|nullable|string',
            'role' => 'sometimes|in:buyer,vendor,admin,mapper',
            'location' => 'sometimes|nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->userService->update($id, $validated),
            'message' => 'User updated',
        ]);
    }

    public function destroy($id)
    {
        $this->userService->suspend($id);

        return response()->json([
            'success' => true,
            'message' => 'User suspended',
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,suspended,pending',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->userService->updateStatus($id, $validated['status']),
            'message' => 'User status updated',
        ]);
    }
}
