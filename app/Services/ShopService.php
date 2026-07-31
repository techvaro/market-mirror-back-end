<?php

namespace App\Services;

use App\Models\Shop;

class ShopService
{
    public function list(array $filters, int $perPage = 15)
    {
        $query = Shop::with(['user', 'products', 'reviews', 'services', 'shopImages']);

        if (isset($filters['market'])) {
            $query->where('market', $filters['market']);
        }
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (isset($filters['city'])) {
            $query->where('city', $filters['city']);
        }
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('market', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): Shop
    {
        return Shop::with(['user', 'products', 'reviews', 'services', 'shopImages'])->findOrFail($id);
    }

    public function create(array $data, int $userId): Shop
    {
        return Shop::create([
            ...$data,
            'user_id' => $userId,
            'rating' => 0,
            'review_count' => 0,
            'product_count' => 0,
        ]);
    }

    public function update(int $id, array $data): Shop
    {
        $shop = Shop::findOrFail($id);
        $shop->update($data);
        return $shop;
    }

    public function delete(int $id): void
    {
        Shop::findOrFail($id)->delete();
    }

    public function reviews(int $shopId, int $perPage = 15)
    {
        $shop = Shop::findOrFail($shopId);
        return $shop->reviews()->with('user')->latest()->paginate($perPage);
    }
}
