<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateUserRequest;
use App\Http\Requests\Api\UpdateUserStatusRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(Request $request)
    {
        $users = $this->userService->list(
            $request->only(['role', 'status', 'search']),
            $request->get('per_page', 15)
        );

        return $this->paginated(UserResource::collection($users), $users);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($this->userService->find($id)),
        ]);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($this->userService->update($id, $request->sanitized())),
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

    public function updateStatus(UpdateUserStatusRequest $request, $id)
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($this->userService->updateStatus($id, $request->validated('status'))),
            'message' => 'User status updated',
        ]);
    }

    public function approveVendor($id)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => new UserResource($this->userService->approveVendor($id)),
                'message' => 'Vendor approved. An approval email has been sent.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function rejectVendor($id)
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($this->userService->rejectVendor($id)),
            'message' => 'Vendor application rejected.',
        ]);
    }
}
