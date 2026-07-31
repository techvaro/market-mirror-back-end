<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Shop::with(['images']);

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('market')) {
            $query->where('market', $request->market);
        }

        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        $shops = $query->orderByDesc('rating')->paginate(12);

        return response()->json($shops);
    }

    public function show(Shop $shop): JsonResponse
    {
        $shop->load(['images', 'products.variants', 'reviews' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return response()->json($shop);
    }
}
