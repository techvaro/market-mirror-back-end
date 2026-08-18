<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Shop;

class ReviewService
{
    public function list(array $filters, int $perPage = 15)
    {
        $query = Review::with(['shop', 'user', 'reviewImages']);

        if (isset($filters['shop_id'])) {
            $query->where('shop_id', $filters['shop_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data, int $userId, string $userName): Review
    {
        $review = Review::create([
            ...$data,
            'user_id' => $userId,
            'reviewer_name' => $userName,
        ]);

        $this->recalculateShopRating($data['shop_id']);

        return $review->load('shop', 'user');
    }

    public function find(int $id): Review
    {
        return Review::with(['shop', 'user', 'reviewImages'])->findOrFail($id);
    }

    public function reply(int $id, string $reply): Review
    {
        $review = Review::findOrFail($id);
        $review->update(['reply' => $reply]);
        return $review;
    }

    public function delete(int $id): void
    {
        Review::findOrFail($id)->delete();
    }

    private function recalculateShopRating(int $shopId): void
    {
        $shop = Shop::find($shopId);
        if (!$shop) return;

        $avgRating = $shop->reviews()->avg('rating');
        $shop->update([
            'rating' => round($avgRating, 1),
            'review_count' => $shop->reviews()->count(),
        ]);
    }
}
