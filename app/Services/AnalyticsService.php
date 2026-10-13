<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Shop;
use Carbon\Carbon;

class AnalyticsService
{
    public function vendor(int $userId): array
    {
        $shop = Shop::where('user_id', $userId)->first();

        if (! $shop) {
            return $this->emptyVendorStats();
        }

        $totalOrders = Order::whereHas('items', function ($q) use ($shop) {
            $q->where('shop_name', $shop->name);
        })->count();

        $totalProducts = Product::where('shop_id', $shop->id)->count();
        $totalReviews = Review::where('shop_id', $shop->id)->count();
        $avgRating = Review::where('shop_id', $shop->id)->avg('rating') ?? 0;

        $revenue = Order::whereHas('items', function ($q) use ($shop) {
            $q->where('shop_name', $shop->name);
        })->where('status', 'delivered')->sum('total');

        $today = Carbon::today();
        $todaysOrders = Order::whereHas('items', function ($q) use ($shop) {
            $q->where('shop_name', $shop->name);
        })->whereDate('created_at', $today)->count();

        $todaysSales = Order::whereHas('items', function ($q) use ($shop) {
            $q->where('shop_name', $shop->name);
        })->where('status', 'delivered')->whereDate('created_at', $today)->sum('total');

        $pendingOrders = Order::whereHas('items', function ($q) use ($shop) {
            $q->where('shop_name', $shop->name);
        })->whereIn('status', ['confirmed', 'packing'])->count();

        $completedOrders = Order::whereHas('items', function ($q) use ($shop) {
            $q->where('shop_name', $shop->name);
        })->where('status', 'delivered')->count();

        $uniqueCustomers = Order::whereHas('items', function ($q) use ($shop) {
            $q->where('shop_name', $shop->name);
        })->distinct('user_id')->count('user_id');

        $revenue7Days = [];
        $orders7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $revenue7Days[] = (float) Order::whereHas('items', function ($q) use ($shop) {
                $q->where('shop_name', $shop->name);
            })->where('status', 'delivered')->whereDate('created_at', $date)->sum('total');

            $orders7Days[] = Order::whereHas('items', function ($q) use ($shop) {
                $q->where('shop_name', $shop->name);
            })->whereDate('created_at', $date)->count();
        }

        return [
            'totalRevenue' => (float) $revenue,
            'totalOrders' => $totalOrders,
            'totalProducts' => $totalProducts,
            'averageRating' => round($avgRating, 1),
            'totalCustomers' => $uniqueCustomers,
            'growthRate' => 0,
            'todaysSales' => (float) $todaysSales,
            'todaysOrders' => $todaysOrders,
            'pendingOrders' => $pendingOrders,
            'completedOrders' => $completedOrders,
            'revenue7Days' => $revenue7Days,
            'orders7Days' => $orders7Days,
        ];
    }

    public function vendorSales(int $userId): array
    {
        $shop = Shop::where('user_id', $userId)->first();
        if (! $shop) {
            return [];
        }

        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $revenue = (float) Order::whereHas('items', function ($q) use ($shop) {
                $q->where('shop_name', $shop->name);
            })->where('status', 'delivered')->whereDate('created_at', $date)->sum('total');

            $data[] = [
                'label' => $date->format('M d'),
                'value' => $revenue,
            ];
        }

        return $data;
    }

    private function emptyVendorStats(): array
    {
        return [
            'totalRevenue' => 0,
            'totalOrders' => 0,
            'totalProducts' => 0,
            'averageRating' => 0,
            'totalCustomers' => 0,
            'growthRate' => 0,
            'todaysSales' => 0,
            'todaysOrders' => 0,
            'pendingOrders' => 0,
            'completedOrders' => 0,
            'revenue7Days' => [0, 0, 0, 0, 0, 0, 0],
            'orders7Days' => [0, 0, 0, 0, 0, 0, 0],
        ];
    }
}
