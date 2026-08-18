<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, ?Order $order = null): bool
    {
        if ($order === null) {
            return true;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'buyer') {
            return $order->user_id === $user->id;
        }

        if ($user->role === 'vendor') {
            return $order->items()->whereIn('shop_name', $user->shops->pluck('name'))->exists();
        }

        return false;
    }

    public function update(User $user, ?Order $order = null): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($order === null) {
            return $user->role === 'buyer';
        }

        // Buyers may cancel their own confirmed orders.
        if ($user->role === 'buyer') {
            return $order->user_id === $user->id && $order->status === 'confirmed';
        }

        // Vendors may update statuses of orders containing their products.
        if ($user->role === 'vendor') {
            return $order->items()->whereIn('shop_name', $user->shops->pluck('name'))->exists();
        }

        return false;
    }
}
