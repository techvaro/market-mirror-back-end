<?php

namespace App\Services;

use App\Models\User;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Mapper;
use App\Models\Order;

class AdminDashboardService
{
    public function getStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_buyers' => User::where('role', 'buyer')->count(),
            'total_vendors' => User::where('role', 'vendor')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_mappers' => Mapper::count(),
            'active_mappers' => Mapper::where('mapper_status', 'active')->count(),
            'total_shops' => Shop::count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'confirmed')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
        ];
    }
}
