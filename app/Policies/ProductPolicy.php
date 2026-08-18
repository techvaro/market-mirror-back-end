<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'vendor' || $user->role === 'admin';
    }

    public function update(User $user, ?Product $product = null): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($product === null) {
            return $user->role === 'vendor';
        }

        return $product->shop?->user_id === $user->id;
    }

    public function delete(User $user, ?Product $product = null): bool
    {
        return $this->update($user, $product);
    }
}
