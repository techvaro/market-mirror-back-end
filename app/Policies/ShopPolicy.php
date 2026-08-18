<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;

class ShopPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'vendor' || $user->role === 'admin';
    }

    public function update(User $user, ?Shop $shop = null): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($shop === null) {
            return $user->role === 'vendor';
        }

        return $shop->user_id === $user->id;
    }

    public function delete(User $user, ?Shop $shop = null): bool
    {
        return $this->update($user, $shop);
    }
}
