<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Shop;
use App\Models\VendorApplication;
use App\Support\MarketContext;

class SearchService
{
    public function __construct(
        private readonly MarketContext $marketContext
    ) {}

    public function search(string $query, ?float $lat = null, ?float $lng = null, ?float $radius = null): array
    {
        if (strlen($query) < 2) {
            return ['shops' => [], 'products' => [], 'vendors' => []];
        }

        $marketId = $this->marketContext->get();

        $shops = Shop::forMarket($marketId)
            ->when($lat !== null && $lng !== null && $radius !== null, fn ($q) => $q->nearBy($lat, $lng, $radius))
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%")
                    ->orWhere('market', 'like', "%{$query}%")
                    ->orWhere('city', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        $products = Product::forMarket($marketId)
            ->with('shop')
            ->where('stock', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        $vendors = VendorApplication::where('shop_name', 'like', "%{$query}%")
            ->orWhere('owner_name', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return compact('shops', 'products', 'vendors');
    }
}
