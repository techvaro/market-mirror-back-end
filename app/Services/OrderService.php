<?php

namespace App\Services;

use App\Models\Order;

class OrderService
{
    public function list(array $filters, ?string $userRole = null, ?int $userId = null, int $perPage = 15)
    {
        $query = Order::with(['user', 'items.product']);

        if ($userRole === 'buyer' && $userId) {
            $query->where('user_id', $userId);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): Order
    {
        return Order::with(['user', 'items.product'])->findOrFail($id);
    }

    public function create(array $data, int $userId): Order
    {
        $subtotal = collect($data['items'])->sum(function ($item) {
            return ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
        });

        $deliveryFee = $data['delivery_method'] === 'delivery' ? 1500 : 0;

        return Order::create([
            'user_id' => $userId,
            'shop_id' => $data['shop_id'] ?? null,
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'status' => 'confirmed',
            'delivery_method' => $data['delivery_method'],
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $subtotal + $deliveryFee,
            'payment_method' => 'paystack',
            'address_first_name' => $data['address_first_name'] ?? null,
            'address_last_name' => $data['address_last_name'] ?? null,
            'address_phone' => $data['address_phone'] ?? null,
            'address_street' => $data['address_street'] ?? null,
            'address_state' => $data['address_state'] ?? null,
            'address_city' => $data['address_city'] ?? null,
        ]);
    }

    public function update(int $id, array $data): Order
    {
        $order = Order::findOrFail($id);
        $order->update($data);
        return $order;
    }
}
