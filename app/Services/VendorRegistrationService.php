<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\VendorRegistration;
use Carbon\Carbon;

class VendorRegistrationService
{
    public function list(array $filters, int $perPage = 15, ?int $mapperId = null)
    {
        $query = VendorRegistration::with('mapper');

        if ($mapperId !== null) {
            $query->where('mapper_id', $mapperId);
        }

        if (isset($filters['status'])) {
            $query->where('approval_status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function adminList(array $filters, int $perPage = 15)
    {
        $query = VendorRegistration::with('mapper');

        $status = $filters['status'] ?? null;
        if (! empty($status) && $status !== 'all') {
            $query->where('approval_status', $status);
        }

        if (! empty($filters['market'])) {
            $query->where('market', $filters['market']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('vendor_name', 'like', "%{$search}%")
                    ->orWhere('owner_name', 'like', "%{$search}%")
                    ->orWhere('mapper_name', 'like', "%{$search}%");
            });
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

    public function review(int $id, array $data, ?int $adminId = null): VendorRegistration
    {
        $registration = VendorRegistration::findOrFail($id);

        $status = $data['approval_status'];

        $registration->update([
            'approval_status' => $status,
            'current_status' => $status === 'changes_requested' ? 'under_review' : 'finalized',
            'review_notes' => $data['review_notes'] ?? null,
            'reviewed_at' => Carbon::now(),
            'reviewed_by' => $adminId,
        ]);

        $this->notifyMapper($registration, $status, $data['review_notes'] ?? null);

        return $registration->fresh(['mapper']);
    }

    private function notifyMapper(VendorRegistration $registration, string $status, ?string $notes): void
    {
        $userId = $registration->mapper?->user_id;
        if (! $userId) {
            return;
        }

        [$title, $message] = match ($status) {
            'approved' => [
                'Vendor Registration Approved',
                "Your vendor registration for \"{$registration->vendor_name}\" has been approved.",
            ],
            'rejected' => [
                'Vendor Registration Rejected',
                "Your vendor registration for \"{$registration->vendor_name}\" was rejected."
                    .($notes ? " Reason: {$notes}" : ''),
            ],
            default => [
                'Changes Requested',
                "Changes were requested for \"{$registration->vendor_name}\"."
                    .($notes ? " {$notes}" : ''),
            ],
        };

        Notification::create([
            'user_id' => $userId,
            'type' => 'vendor_review',
            'title' => $title,
            'message' => $message,
            'link' => '/verification',
        ]);
    }
}
