<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->orderService->list(
                $request->only(['status']),
                $request->user()->role,
                $request->user()->id,
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->orderService->find($id),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shop_id' => 'nullable|exists:shops,id',
            'delivery_method' => 'required|in:delivery,pickup',
            'address_first_name' => 'nullable|string',
            'address_last_name' => 'nullable|string',
            'address_phone' => 'nullable|string',
            'address_street' => 'nullable|string',
            'address_state' => 'nullable|string',
            'address_city' => 'nullable|string',
            'items' => 'required|array',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->orderService->create($validated, $request->user()->id),
            'message' => 'Order created',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:confirmed,packing,out_for_delivery,delivered,cancelled',
            'cancellation_reason' => 'nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->orderService->update($id, $validated),
            'message' => 'Order updated',
        ]);
    }
}
