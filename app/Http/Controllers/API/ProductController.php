<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['shop', 'variants']);

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->boolean('in_stock')) {
            $query->where('stock', true);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $sortBy = $request->input('sort', 'recommended');
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate($request->input('per_page', 12));

        return response()->json($products);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['shop', 'variants']);

        return response()->json($product);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        if (empty($query)) {
            return response()->json(['products' => [], 'shops' => []]);
        }

        $products = Product::with(['shop'])
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit(20)
            ->get();

        $shops = \App\Models\Shop::with(['images'])
            ->where('name', 'like', "%{$query}%")
            ->orWhere('category', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json([
            'products' => $products,
            'shops' => $shops,
            'query' => $query,
        ]);
    }
}
