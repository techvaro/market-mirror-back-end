<?php

namespace App\Services;

use App\Models\Market;
use App\Models\Shop;
use App\Support\MarketContext;

class ShopService
{
    public function __construct(
        private readonly MarketContext $marketContext
    ) {}

    public function list(array $filters, int $perPage = 15)
    {
        $query = Shop::with(['user', 'products', 'reviews', 'services', 'shopImages'])
            ->forMarket($this->marketContext->get());

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
        if (isset($filters['lat'], $filters['lng'], $filters['radius'])) {
            $query->nearBy((float) $filters['lat'], (float) $filters['lng'], (float) $filters['radius']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): Shop
    {
        return Shop::with(['user', 'products', 'reviews', 'services', 'shopImages'])
            ->forMarket($this->marketContext->get())
            ->findOrFail($id);
    }

    /**
     * Unscoped lookup used by vendors/admins for ownership-scoped mutations.
     */
    public function findForOwner(int $id): Shop
    {
        return Shop::with(['user', 'products', 'reviews', 'services', 'shopImages'])->findOrFail($id);
    }

    public function create(array $data, int $userId): Shop
    {
        $marketId = $this->resolveMarketId($data);

        $shop = Shop::create([
            ...$data,
            'user_id' => $userId,
            'market_id' => $marketId,
            'rating' => 0,
            'review_count' => 0,
            'product_count' => 0,
        ]);

        if ($marketId !== null) {
            Market::whereKey($marketId)->increment('shop_count');
        }

        return $shop->load('user', 'market');
    }

    public function update(int $id, array $data): Shop
    {
        $shop = Shop::findOrFail($id);
        $marketId = $this->resolveMarketId($data);
        $data['market_id'] = $marketId ?? $shop->market_id;

        $shop->update($data);
        return $shop->load('user', 'market');
    }

    public function delete(int $id): void
    {
        $shop = Shop::findOrFail($id);

        if ($shop->market_id !== null) {
            Market::whereKey($shop->market_id)->decrement('shop_count');
        }

        $shop->delete();
    }

    private function resolveMarketId(array $data): ?int
    {
        if (isset($data['market_id']) && $data['market_id'] !== null) {
            return (int) $data['market_id'];
        }

        if (isset($data['market']) && $data['market'] !== '') {
            $market = Market::where('name', $data['market'])->first();

            if ($market) {
                return $market->id;
            }

            $market = Market::create([
                'name' => $data['market'],
                'location' => $data['market'],
                'city' => $data['city'] ?? '',
            ]);

            return $market->id;
        }

        return null;
    }

    public function reviews(int $shopId, int $perPage = 15)
    {
        $shop = Shop::findOrFail($shopId);
        return $shop->reviews()->with(['user', 'reviewImages'])->latest()->paginate($perPage);
    }
}
