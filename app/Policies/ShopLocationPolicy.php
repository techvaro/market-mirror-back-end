<?php

namespace App\Policies;

use App\Models\ShopLocation;
use App\Models\User;

class ShopLocationPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'mapper' || $user->role === 'admin';
    }

    public function update(User $user, ?ShopLocation $location = null): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($location === null) {
            return $user->role === 'mapper';
        }

        return $location->mapper_id === $user->mapperProfile?->id;
    }
}
