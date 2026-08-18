<?php

namespace App\Policies;

use App\Models\Dispute;
use App\Models\User;

class DisputePolicy
{
    public function view(User $user, ?Dispute $dispute = null): bool
    {
        if ($dispute === null) {
            return true;
        }

        return $user->role === 'admin' || $dispute->user_id === $user->id;
    }

    public function update(User $user, ?Dispute $dispute = null): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($dispute === null) {
            return false;
        }

        return $dispute->user_id === $user->id;
    }

    public function resolve(User $user): bool
    {
        return $user->role === 'admin';
    }
}
