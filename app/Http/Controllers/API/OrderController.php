<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with('items')
            ->latest('placed_at')
            ->paginate(20);

        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'delivery_method' => 'required|in:delivery,pickup',
            'payment_method' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.name' => 'required|string',
            'items.*.variant' => 'required|string',
            'items.*.price' => 'required|integer|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.color' => 'nullable|string',
            'items.*.shop_name' => 'required|string',
            'subtotal' => 'required|integer|min:0',
            'delivery_fee' => 'required|integer|min:0',
            'total' => 'required|integer|min:0',
            'address.first_name' => 'required_if:delivery_method,delivery|nullable|string',
            'address.last_name' => 'required_if:delivery_method,delivery|nullable|string',
            'address.phone' => 'required_if:delivery_method,delivery|nullable|string',
            'address.street' => 'required_if:delivery_method,delivery|nullable|string',
            'address.state' => 'required_if:delivery_method,delivery|nullable|string',
            'address.city' => 'required_if:delivery_method,delivery|nullable|string',
        ]);

        $orderNumber = 'MM-' . mt_rand(10000, 99999) . '-' . mt_rand(1000, 9999);

        $order = $request->user()->orders()->create([
            'order_number' => $orderNumber,
            'status' => 'confirmed',
            'delivery_method' => $validated['delivery_method'],
            'payment_method' => $validated['payment_method'],
            'subtotal' => $validated['subtotal'],
            'delivery_fee' => $validated['delivery_fee'],
            'total' => $validated['total'],
            'estimated_delivery' => now()->addDays(2)->format('D, M j') . ', 10am - 4pm',
            'address_first_name' => $validated['address']['first_name'] ?? null,
            'address_last_name' => $validated['address']['last_name'] ?? null,
            'address_phone' => $validated['address']['phone'] ?? null,
            'address_street' => $validated['address']['street'] ?? null,
            'address_state' => $validated['address']['state'] ?? null,
            'address_city' => $validated['address']['city'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'variant' => $item['variant'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'color' => $item['color'] ?? null,
                'shop_name' => $item['shop_name'],
            ]);
        }

        $order->load('items');

        return response()->json($order, 201);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        $order->load('items');

        return response()->json($order);
    }
}
