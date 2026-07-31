<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\Product;
use App\Models\VendorApplication;

class SearchService
{
    public function search(string $query): array
    {
        if (strlen($query) < 2) {
            return ['shops' => [], 'products' => [], 'vendors' => []];
        }

        $shops = Shop::where('name', 'like', "%{$query}%")
            ->orWhere('category', 'like', "%{$query}%")
            ->orWhere('market', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        $vendors = VendorApplication::where('shop_name', 'like', "%{$query}%")
            ->orWhere('owner_name', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return compact('shops', 'products', 'vendors');
    }
}
