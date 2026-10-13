<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VendorRegistration;

class VendorRegistrationPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'mapper' || $user->role === 'admin';
    }

    public function review(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, ?VendorRegistration $registration = null): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($registration === null) {
            return $user->role === 'mapper';
        }

        return $registration->mapper_id === $user->mapperProfile?->id;
    }
}
