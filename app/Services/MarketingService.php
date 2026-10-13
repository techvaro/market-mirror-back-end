<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\FlashSale;

class MarketingService
{
    // ─── Coupons ──────────────────────────────────────────────────
    public function listCoupons(int $userId, int $perPage = 15)
    {
        return Coupon::where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function createCoupon(array $data, int $userId): Coupon
    {
        return Coupon::create([
            ...$data,
            'user_id' => $userId,
        ]);
    }

    public function updateCoupon(int $id, array $data): Coupon
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update($data);

        return $coupon;
    }

    public function deleteCoupon(int $id): void
    {
        Coupon::findOrFail($id)->delete();
    }

    // ─── Flash Sales ──────────────────────────────────────────────
    public function listFlashSales(int $userId, int $perPage = 15)
    {
        return FlashSale::where('user_id', $userId)
            ->with('product')
            ->latest()
            ->paginate($perPage);
    }

    public function createFlashSale(array $data, int $userId): FlashSale
    {
        return FlashSale::create([
            ...$data,
            'user_id' => $userId,
        ]);
    }

    public function updateFlashSale(int $id, array $data): FlashSale
    {
        $sale = FlashSale::findOrFail($id);
        $sale->update($data);

        return $sale;
    }

    public function deleteFlashSale(int $id): void
    {
        FlashSale::findOrFail($id)->delete();
    }
}
