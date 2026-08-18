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

    public function update(int $id, array $data): Order
    {
        $order = Order::findOrFail($id);
        $order->update($data);
        return $order;
    }
}
