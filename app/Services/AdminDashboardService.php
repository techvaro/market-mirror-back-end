<?php

namespace App\Services;

use App\Models\Mapper;
use App\Models\Market;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Models\VendorApplication;
use Carbon\Carbon;

class AdminDashboardService
{
    public function getStats(): array
    {
        $periodStart = Carbon::now()->subDays(6)->startOfDay();
        $orders = Order::query()
            ->where('created_at', '>=', $periodStart)
            ->whereIn('status', ['confirmed', 'packing', 'out_for_delivery', 'delivered'])
            ->get(['created_at', 'total']);

        $revenueTrend = collect(range(0, 6))->map(function (int $daysAgo) use ($orders) {
            $date = Carbon::now()->subDays(6 - $daysAgo);
            $dayOrders = $orders->filter(fn (Order $order) => $order->created_at->isSameDay($date));

            return [
                'date' => $date->format('M j'),
                'revenue' => (int) $dayOrders->sum('total'),
                'orders' => $dayOrders->count(),
            ];
        })->values()->all();

        return [
            'total_users' => User::count(),
            'total_buyers' => User::where('role', 'buyer')->count(),
            'total_vendors' => User::where('role', 'vendor')->count(),
            'active_vendors' => User::where('role', 'vendor')->where('status', 'active')->count(),
            'pending_vendors' => VendorApplication::whereIn('status', ['Pending Review', 'pending_review'])->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_mappers' => Mapper::count(),
            'active_mappers' => Mapper::where('mapper_status', 'active')->count(),
            'total_shops' => Shop::count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::whereIn('status', ['confirmed', 'packing', 'out_for_delivery'])->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'revenue_trend' => $revenueTrend,
        ];
    }

    public function getAnalytics(): array
    {
        $orders = Order::with('items.product.shop.market')->get(['id', 'total', 'created_at']);
        $now = Carbon::now();
        $currentStart = $now->copy()->subDays(30);
        $previousStart = $now->copy()->subDays(60);
        $currentOrders = $orders->filter(fn (Order $order) => $order->created_at->greaterThanOrEqualTo($currentStart));
        $previousOrders = $orders->filter(fn (Order $order) => $order->created_at->betweenIncluded($previousStart, $currentStart));
        $growth = static function (int|float $current, int|float $previous): float {
            return $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : 0;
        };

        $marketTotals = [];
        $categoryTotals = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $revenue = (int) $item->price * (int) $item->quantity;
                $shop = $item->product?->shop;
                $market = $shop?->getRelation('market');
                if (! $market instanceof Market && $shop?->market_id) {
                    $market = Market::find($shop->market_id);
                }
                if ($market instanceof Market) {
                    $marketTotals[$market->id]['name'] = $market->name;
                    $marketTotals[$market->id]['revenue'] = ($marketTotals[$market->id]['revenue'] ?? 0) + $revenue;
                    $marketTotals[$market->id]['orders'] = ($marketTotals[$market->id]['orders'] ?? 0) + 1;
                }
                $category = $item->product?->category;
                if ($category) {
                    $categoryTotals[$category] = ($categoryTotals[$category] ?? 0) + $revenue;
                }
            }
        }

        $categoryRevenue = array_sum($categoryTotals);
        $markets = Market::withCount('shops')->get()->map(function (Market $market) use ($marketTotals) {
            $stats = $marketTotals[$market->id] ?? [];

            return [
                'name' => $market->name,
                'revenue' => (int) ($stats['revenue'] ?? 0),
                'orders' => (int) ($stats['orders'] ?? 0),
                'vendors' => (int) $market->shops_count,
            ];
        })->sortByDesc('revenue')->values()->all();

        $categories = collect($categoryTotals)->map(function (int|float $revenue, string $name) use ($categoryRevenue) {
            return [
                'name' => $name,
                'revenue' => (int) $revenue,
                'percentage' => $categoryRevenue > 0 ? round(($revenue / $categoryRevenue) * 100, 1) : 0,
            ];
        })->sortByDesc('revenue')->values()->all();

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => (int) $orders->sum('total'),
            'total_vendors' => User::where('role', 'vendor')->where('status', 'active')->count(),
            'total_buyers' => User::where('role', 'buyer')->count(),
            'order_growth' => $growth($currentOrders->count(), $previousOrders->count()),
            'revenue_growth' => $growth($currentOrders->sum('total'), $previousOrders->sum('total')),
            'markets' => $markets,
            'categories' => $categories,
        ];
    }
}
