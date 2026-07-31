<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ShopLocationService;
use Illuminate\Http\Request;

class ShopLocationController extends Controller
{
    public function __construct(
        private readonly ShopLocationService $shopLocationService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->shopLocationService->list(
                $request->only(['status', 'market']),
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_name' => 'required|string',
            'market' => 'required|string',
            'street' => 'nullable|string',
            'shop_block' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'required|in:high,medium,low',
            'mapper_name' => 'nullable|string',
            'photos' => 'nullable|array',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->shopLocationService->create($validated, $request->user()->mapperProfile),
            'message' => 'Location submitted',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,re_mapping_requested',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->shopLocationService->update($id, $validated),
            'message' => 'Location updated',
        ]);
    }
}
