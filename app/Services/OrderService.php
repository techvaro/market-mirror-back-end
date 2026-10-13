<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private readonly RefundService $refundService) {}

    public function list(array $filters, ?string $userRole = null, ?int $userId = null, int $perPage = 15)
    {
        $query = Order::with(['user', 'items.product']);

        if ($userRole === 'buyer' && $userId) {
            $query->where('user_id', $userId);
        }

        if ($userRole === 'vendor' && $userId) {
            $shopNames = Shop::where('user_id', $userId)->pluck('name');

            if ($shopNames->isEmpty()) {
                return Order::with(['user', 'items.product'])->whereRaw('1 = 0')->paginate($perPage);
            }

            $query->whereHas('items', fn ($q) => $q->whereIn('shop_name', $shopNames));
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
        $order = DB::transaction(function () use ($id, $data) {
            $order = Order::lockForUpdate()->findOrFail($id);
            $oldStatus = $order->status;
            $order->update($data);

            if (isset($data['status']) && $data['status'] !== $oldStatus) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $data['status'],
                    'note' => $data['cancellation_reason'] ?? "Order status updated to {$data['status']}",
                ]);

                $statusLabels = [
                    'confirmed' => 'Order Confirmed',
                    'packing' => 'Order Being Packed',
                    'out_for_delivery' => 'Out for Delivery',
                    'delivered' => 'Order Delivered',
                    'cancelled' => 'Order Cancelled',
                ];

                Notification::create([
                    'user_id' => $order->user_id,
                    'type' => 'order',
                    'title' => $statusLabels[$data['status']] ?? 'Order Updated',
                    'message' => "Your order {$order->order_number} has been updated to: {$data['status']}",
                    'link' => "/orders/{$order->id}",
                ]);
            }

            return $order;
        });

        if (($data['status'] ?? null) === 'cancelled') {
            $this->refundService->refundOrder($order->load('user'));
        }

        return $order;
    }

    public function notifyVendors(Order $order): void
    {
        $vendorIds = $order->items()
            ->with('product.shop')
            ->get()
            ->map(fn ($item) => $item->product?->shop?->user_id)
            ->filter()
            ->unique();

        if ($vendorIds->isEmpty()) {
            $vendorIds = Shop::whereIn(
                'name',
                $order->items()->pluck('shop_name')->unique()
            )->pluck('user_id')->unique();
        }

        foreach ($vendorIds as $vendorId) {
            Notification::create([
                'user_id' => $vendorId,
                'type' => 'order',
                'title' => 'New Order Received',
                'message' => "Order {$order->order_number} has been placed and is awaiting payment confirmation.",
                'link' => "/orders/{$order->id}",
            ]);
        }
    }
}
