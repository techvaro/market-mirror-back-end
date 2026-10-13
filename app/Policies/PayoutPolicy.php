<?php

namespace App\Policies;

use App\Models\Payout;
use App\Models\User;

class PayoutPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['vendor', 'admin']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['vendor', 'admin']);
    }

    public function view(User $user, Payout $payout): bool
    {
        return $user->role === 'admin' || $user->id === $payout->user_id;
    }

    public function manageBankDetails(User $user): bool
    {
        return in_array($user->role, ['buyer', 'vendor', 'admin']);
    }

    public function approve(User $user): bool
    {
        return $user->role === 'admin';
    }
}
