<?php

namespace App\Services;

use App\Models\Payout;

class PayoutService
{
    public function list(int $userId, int $perPage = 15)
    {
        return Payout::where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function find(int $id): Payout
    {
        return Payout::findOrFail($id);
    }

    public function create(array $data, int $userId): Payout
    {
        return Payout::create([
            ...$data,
            'user_id' => $userId,
            'status' => 'pending',
        ]);
    }
}
