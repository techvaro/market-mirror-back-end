<?php

namespace App\Policies;

use App\Models\FlashSale;
use App\Models\User;

class FlashSalePolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'vendor' || $user->role === 'admin';
    }

    public function update(User $user, ?FlashSale $sale = null): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($sale === null) {
            return $user->role === 'vendor';
        }

        return $sale->user_id === $user->id;
    }

    public function delete(User $user, ?FlashSale $sale = null): bool
    {
        return $this->update($user, $sale);
    }
}
