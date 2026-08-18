<?php

namespace App\Policies;

use App\Models\Coupon;
use App\Models\User;

class CouponPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'vendor' || $user->role === 'admin';
    }

    public function update(User $user, ?Coupon $coupon = null): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($coupon === null) {
            return $user->role === 'vendor';
        }

        return $coupon->user_id === $user->id;
    }

    public function delete(User $user, ?Coupon $coupon = null): bool
    {
        return $this->update($user, $coupon);
    }
}
