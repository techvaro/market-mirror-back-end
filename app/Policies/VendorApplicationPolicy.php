<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VendorApplication;

class VendorApplicationPolicy
{
    public function update(User $user, ?VendorApplication $application = null): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($application === null) {
            return $user->role === 'vendor' || $user->role === 'buyer';
        }

        return $application->user_id === $user->id;
    }
}
