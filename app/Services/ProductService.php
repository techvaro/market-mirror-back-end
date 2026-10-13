<?php

namespace App\Services;

use App\Models\Product;
use App\Support\MarketContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        private readonly MarketContext $marketContext
    ) {}

    public function list(array $filters, int $perPage = 15, bool $includeUnpublished = false)
    {
        $query = Product::with(['shop', 'variants', 'images'])
            ->forMarket($this->marketContext->get());

        if (! $includeUnpublished) {
            $query->where('stock', true);
        }

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

        $sort = $filters['sort'] ?? 'latest';
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'rating' => $query->orderByDesc('rating'),
            default => $query->latest(),
        };

        return $query->paginate($perPage);
    }

    public function find(int $id, bool $includeUnpublished = false): Product
    {
        $query = Product::with(['shop', 'variants', 'images'])
            ->forMarket($this->marketContext->get())
            ->when(! $includeUnpublished, fn (Builder $query) => $query->where('stock', true));

        return $query->findOrFail($id);
    }

    /**
     * Unscoped lookup used by vendors/admins for ownership-scoped mutations
     * (update/delete) regardless of the active buyer market context.
     */
    public function findForOwner(int $id): Product
    {
        return Product::with(['shop', 'variants', 'images'])->findOrFail($id);
    }

    public function create(array $data): Product
    {
        $data['sku'] ??= $this->generateSku();
        $product = Product::create($data);

        if ($product->shop) {
            $product->shop->increment('product_count');
        }

        return $product->load(['shop', 'variants', 'images']);
    }

    private function generateSku(): string
    {
        do {
            $sku = 'MM-SKU-'.strtoupper(Str::random(10));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    public function update(int $id, array $data): Product
    {
        $product = Product::findOrFail($id);
        $product->update($data);

        return $product->load(['shop', 'variants', 'images']);
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
