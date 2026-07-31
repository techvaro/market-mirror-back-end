<?php

namespace App\Services;

use App\Models\Payment;

class PaymentService
{
    public function list(array $filters, ?string $userRole = null, ?int $userId = null, int $perPage = 15)
    {
        $query = Payment::with(['user', 'order']);

        if ($userRole !== 'admin' && $userId) {
            $query->where('user_id', $userId);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data, int $userId): Payment
    {
        return Payment::create([
            ...$data,
            'user_id' => $userId,
            'status' => 'completed',
        ]);
    }
}
