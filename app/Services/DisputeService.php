<?php

namespace App\Services;

use App\Models\Dispute;

class DisputeService
{
    public function list(array $filters, ?string $userRole = null, ?int $userId = null, int $perPage = 15)
    {
        $query = Dispute::with(['user', 'order']);

        if ($userRole !== 'admin' && $userId) {
            $query->where('user_id', $userId);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data, int $userId): Dispute
    {
        return Dispute::create([
            ...$data,
            'user_id' => $userId,
            'status' => 'Under Review',
        ])->load('order', 'user');
    }

    public function find(int $id): Dispute
    {
        return Dispute::with(['user', 'order'])->findOrFail($id);
    }

    public function update(int $id, array $data): Dispute
    {
        $dispute = Dispute::findOrFail($id);
        $dispute->update($data);
        return $dispute;
    }
}
