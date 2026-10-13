<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Shop;

class PaymentService
{
    public function list(array $filters, ?string $userRole = null, ?int $userId = null, int $perPage = 15)
    {
        $query = Payment::with(['user', 'order']);

        if ($userRole === 'vendor' && $userId) {
            $shopNames = Shop::where('user_id', $userId)->pluck('name');

            if ($shopNames->isEmpty()) {
                return Payment::with(['user', 'order'])->whereRaw('1 = 0')->paginate($perPage);
            }

            $orderIds = OrderItem::whereIn('shop_name', $shopNames)->pluck('order_id');
            $query->whereIn('order_id', $orderIds);
        } elseif ($userRole !== 'admin' && $userId) {
            $query->where('user_id', $userId);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data, int $userId): Payment
    {
        throw new \RuntimeException('Payments must be initialized through Flutterwave.');
    }
}
