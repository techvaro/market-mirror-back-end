<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    public function list(array $filters, int $perPage = 15)
    {
        $query = Product::with(['shop', 'variants']);

        if (isset($filters['shop_id'])) {
            $query->where('shop_id', $filters['shop_id']);
        }
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $query->where('stock', true);
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): Product
    {
        return Product::with(['shop', 'variants'])->findOrFail($id);
    }

    public function create(array $data): Product
    {
        $product = Product::create($data);

        if ($product->shop) {
            $product->shop->increment('product_count');
        }

        return $product;
    }

    public function update(int $id, array $data): Product
    {
        $product = Product::findOrFail($id);
        $product->update($data);
        return $product;
    }

    public function delete(int $id): void
    {
        $product = Product::findOrFail($id);
        $shop = $product->shop;
        $product->delete();

        if ($shop) {
            $shop->decrement('product_count');
        }
    }
}
