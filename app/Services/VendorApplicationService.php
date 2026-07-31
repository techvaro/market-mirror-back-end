<?php

namespace App\Services;

use App\Models\VendorApplication;

class VendorApplicationService
{
    public function list(array $filters, int $perPage = 15)
    {
        $query = VendorApplication::with('user');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data, int $userId): VendorApplication
    {
        return VendorApplication::create([
            ...$data,
            'user_id' => $userId,
            'status' => 'Pending Review',
        ]);
    }

    public function update(int $id, array $data): VendorApplication
    {
        $application = VendorApplication::findOrFail($id);
        $application->update($data);
        return $application;
    }
}
