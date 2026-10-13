<?php

namespace App\Actions\Orders;

use App\Models\CartItem;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use App\Support\MarketContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Builds an order from either an explicit item list or the buyer's cart,
 * persists the order + line items atomically and clears the purchased items
 * from the cart.
 */
class CreateOrderAction
{
    public function __construct(
        private readonly MarketContext $marketContext,
        private readonly OrderService $orderService
    ) {}

    public function execute(array $data, int $userId): Order
    {
        return DB::transaction(function () use ($data, $userId) {
            $items = $this->resolveItems($data, $userId);

            if (empty($items)) {
                throw ValidationException::withMessages([
                    'items' => ['Your cart is empty.'],
                ]);
            }

            $this->assertItemsInActiveMarket($items);

            $subtotal = collect($items)->sum(fn ($item) => $item['price'] * $item['quantity']);
            $deliveryFee = ($data['delivery_method'] ?? 'delivery') === 'delivery' ? 1500 : 0;
            $total = $subtotal + $deliveryFee;

            $order = Order::create([
                'user_id' => $userId,
                'order_number' => 'ORD-'.strtoupper(uniqid()),
                // The order is not confirmed until Flutterwave independently verifies payment.
                'status' => 'payment_pending',
                'delivery_method' => $data['delivery_method'] ?? 'delivery',
                'payment_method' => $data['payment_method'] ?? 'paystack',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'address_first_name' => $data['address_first_name'] ?? null,
                'address_last_name' => $data['address_last_name'] ?? null,
                'address_phone' => $data['address_phone'] ?? null,
                'address_street' => $data['address_street'] ?? null,
                'address_state' => $data['address_state'] ?? null,
                'address_city' => $data['address_city'] ?? null,
            ]);

            $order->items()->createMany($items);

            $this->orderService->notifyVendors($order);

            Notification::create([
                'user_id' => $userId,
                'type' => 'order',
                'title' => 'Order Placed',
                'message' => "Your order {$order->order_number} has been placed and is awaiting payment confirmation.",
                'link' => "/orders/{$order->id}",
            ]);

            return $order->load(['user', 'items.product']);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveItems(array $data, int $userId): array
    {
        if (isset($data['items']) && is_array($data['items']) && count($data['items']) > 0) {
            return array_map(function (array $item) {
                $product = Product::with('shop')->findOrFail($item['product_id']);

                return [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'variant' => $item['variant'] ?? '',
                    'price' => (int) $product->price,
                    'quantity' => (int) ($item['quantity'] ?? 1),
                    'color' => $product->color,
                    'shop_name' => $product->shop?->name ?? 'Market Mirror',
                ];
            }, $data['items']);
        }

        return CartItem::with('product.shop')
            ->where('user_id', $userId)
            ->get()
            ->map(function (CartItem $item) {
                $product = $item->product;

                return [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'variant' => $item->variant ?? '',
                    'price' => (int) $product->price,
                    'quantity' => (int) $item->quantity,
                    'color' => $product->color,
                    'shop_name' => $product->shop?->name ?? 'Market Mirror',
                ];
            })
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function assertItemsInActiveMarket(array $items): void
    {
        if (! $this->marketContext->has()) {
            return;
        }

        $productIds = collect($items)->pluck('product_id')->all();

        $inMarket = Product::whereKey($productIds)
            ->whereHas('shop', fn ($q) => $q->where('market_id', $this->marketContext->get()))
            ->count();

        if ($inMarket !== count($productIds)) {
            throw ValidationException::withMessages([
                'items' => ['One or more items do not belong to the selected market.'],
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function clearPurchasedItems(int $userId, array $items): void
    {
        $productIds = collect($items)->pluck('product_id')->all();

        CartItem::where('user_id', $userId)
            ->whereIn('product_id', $productIds)
            ->delete();
    }
}
