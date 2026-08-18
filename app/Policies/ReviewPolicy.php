<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function delete(User $user, ?Review $review = null): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($review === null) {
            return false;
        }

        if ($review->user_id === $user->id) {
            return true;
        }

        return $review->shop?->user_id === $user->id;
    }

    public function reply(User $user, ?Review $review = null): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($review === null) {
            return $user->role === 'vendor';
        }

        return $review->shop?->user_id === $user->id;
    }

    public function uploadImage(User $user, ?Review $review = null): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($review === null) {
            return false;
        }

        if ($review->user_id === $user->id) {
            return true;
        }

        return $review->shop?->user_id === $user->id;
    }
}
