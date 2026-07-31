<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->first();

        if (!$shop) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $customers = Order::whereHas('items', function ($q) use ($shop) {
                $q->where('shop_name', $shop->name);
            })
            ->with('user')
            ->selectRaw('user_id, COUNT(*) as orders_count, SUM(total) as total_spent, MAX(created_at) as last_order')
            ->groupBy('user_id')
            ->paginate($request->get('per_page', 15));

        $data = $customers->map(function ($order) {
            $user = $order->user;
            return [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'location' => $user->location ?? '',
                'address' => '',
                'ordersCount' => (int) $order->orders_count,
                'totalSpend' => (float) $order->total_spent,
                'lastOrder' => $order->last_order,
                'status' => 'active',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
        ]);
    }
}
