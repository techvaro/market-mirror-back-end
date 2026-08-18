<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Support\MarketContext;

class CategoryService
{
    public function __construct(
        private readonly MarketContext $marketContext
    ) {}

    public function list()
    {
        $query = Category::where('active', true);

        if ($this->marketContext->has()) {
            $marketCategories = Product::forMarket($this->marketContext->get())
                ->distinct()
                ->pluck('category');

            $query->whereIn('name', $marketCategories);
        }

        return $query->orderBy('name')->get();
    }
}
