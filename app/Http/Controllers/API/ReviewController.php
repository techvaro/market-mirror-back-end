<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Shop $shop): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'text' => 'required|string|max:1000',
        ]);

        $review = Review::create([
            'shop_id' => $shop->id,
            'user_id' => $request->user()?->id,
            'reviewer_name' => $request->user()?->name ?? $request->input('reviewer_name', 'Anonymous'),
            'rating' => $validated['rating'],
            'text' => $validated['text'],
        ]);

        $shop->increment('review_count');
        $shop->update([
            'rating' => round($shop->reviews()->avg('rating'), 1),
        ]);

        return response()->json($review, 201);
    }

    public function index(Shop $shop): JsonResponse
    {
        $reviews = $shop->reviews()->latest()->paginate(10);

        return response()->json($reviews);
    }
}
