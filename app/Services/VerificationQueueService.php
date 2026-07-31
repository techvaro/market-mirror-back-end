<?php

namespace App\Services;

use App\Models\VerificationQueue;

class VerificationQueueService
{
    public function list(array $filters, int $perPage = 15)
    {
        $query = VerificationQueue::with('mapper');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function update(int $id, array $data): VerificationQueue
    {
        $item = VerificationQueue::findOrFail($id);
        $item->update($data);
        return $item;
    }
}
