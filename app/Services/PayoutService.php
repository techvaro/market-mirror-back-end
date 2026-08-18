<?php

namespace App\Services;

use App\Models\Payout;
use App\Models\User;

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

    public function findForUser(int $id, int $userId): Payout
    {
        return Payout::where('user_id', $userId)->findOrFail($id);
    }

    public function create(array $data, User $user): Payout
    {
        return Payout::create([
            ...$data,
            'user_id' => $user->id,
            'status' => 'pending',
            'account_name' => $data['account_name'] ?? $user->bank_account_name,
            'account_number' => $data['account_number'] ?? $user->bank_account_number,
            'bank_name' => $data['bank_name'] ?? $user->bank_name,
        ]);
    }

    public function updateBankDetails(int $userId, array $data): User
    {
        $user = User::findOrFail($userId);
        $user->update($data);

        return $user;
    }
}
