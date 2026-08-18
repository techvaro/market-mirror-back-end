<?php

namespace App\Services;

use App\Models\VendorRegistration;

class VendorRegistrationService
{
    public function list(array $filters, int $perPage = 15)
    {
        $query = VendorRegistration::with('mapper');

        if (isset($filters['status'])) {
            $query->where('approval_status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data, ?object $mapperProfile): VendorRegistration
    {
        return VendorRegistration::create([
            ...$data,
            'mapper_id' => $mapperProfile?->id,
            'mapper_name' => $mapperProfile->user->name ?? '',
            'current_status' => 'submitted',
            'approval_status' => 'pending_review',
        ]);
    }

    public function find(int $id): VendorRegistration
    {
        return VendorRegistration::with('mapper')->findOrFail($id);
    }

    public function update(int $id, array $data): VendorRegistration
    {
        $registration = VendorRegistration::findOrFail($id);

        if (($data['approval_status'] ?? '') === 'approved') {
            $data['current_status'] = 'finalized';
        }

        $registration->update($data);
        return $registration;
    }
}
