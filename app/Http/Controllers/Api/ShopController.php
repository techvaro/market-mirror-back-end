<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ShopService;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function __construct(
        private readonly ShopService $shopService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->shopService->list(
                $request->only(['market', 'category', 'city', 'user_id', 'search']),
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->shopService->find($id),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'location' => 'required|string',
            'market' => 'required|string',
            'city' => 'required|string',
            'phone' => 'required|string',
            'shop_number' => 'nullable|string',
            'hours' => 'nullable|string',
            'banner_gradient' => 'nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->shopService->create($validated, $request->user()->id),
            'message' => 'Shop created',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string',
            'description' => 'sometimes|nullable|string',
            'location' => 'sometimes|string',
            'market' => 'sometimes|string',
            'city' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'shop_number' => 'sometimes|nullable|string',
            'hours' => 'sometimes|nullable|string',
            'banner_gradient' => 'sometimes|nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->shopService->update($id, $validated),
            'message' => 'Shop updated',
        ]);
    }

    public function destroy($id)
    {
        $this->shopService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Shop deleted',
        ]);
    }

    public function reviews($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->shopService->reviews($id),
        ]);
    }
}
