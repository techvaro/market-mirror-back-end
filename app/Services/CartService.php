<?php

namespace App\Services;

use App\Models\CartItem;
use Illuminate\Support\Collection;

class CartService
{
    public function list(int $userId): Collection
    {
        return CartItem::with('product.shop')
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function add(array $data, int $userId): CartItem
    {
        $existing = CartItem::where('user_id', $userId)
            ->where('product_id', $data['product_id'])
            ->where('variant', $data['variant'] ?? null)
            ->first();

        if ($existing) {
            $existing->increment('quantity', $data['quantity'] ?? 1);
            return $existing->fresh()->load('product.shop');
        }

        return CartItem::create([
            ...$data,
            'user_id' => $userId,
        ])->load('product.shop');
    }

    public function updateQuantity(int $userId, int $productId, array $data): CartItem
    {
        $item = CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variant', $data['variant'] ?? null)
            ->firstOrFail();

        $item->update(['quantity' => $data['quantity']]);
        return $item->fresh()->load('product.shop');
    }

    public function remove(int $userId, int $productId, ?string $variant = null): void
    {
        $query = CartItem::where('user_id', $userId)->where('product_id', $productId);

        if ($variant !== null) {
            $query->where('variant', $variant);
        }

        $query->delete();
    }

    public function clear(int $userId): void
    {
        CartItem::where('user_id', $userId)->delete();
    }
}
