<?php

namespace App\Services;

use App\Mail\VendorApprovedMail;
use App\Models\Market;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserService
{
    public function list(array $filters, int $perPage = 15)
    {
        $query = User::query();

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->with('shops.market')->latest()->paginate($perPage);
    }

    public function find(int $id): User
    {
        return User::with(['shops', 'orders', 'mapperProfile'])->findOrFail($id);
    }

    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);

        return $user;
    }

    public function suspend(int $id): User
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'suspended']);

        return $user;
    }

    public function updateStatus(int $id, string $status): User
    {
        $user = User::findOrFail($id);
        $user->update(['status' => $status]);

        return $user;
    }

    public function approveVendor(int $id): User
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'vendor') {
            throw new \InvalidArgumentException('Only vendor accounts can be approved.');
        }

        $user->update(['status' => 'active']);
        $user->vendorApplications()->latest('id')->first()?->update(['status' => 'Approved']);

        $shop = $user->shops()->latest('id')->first();

        Notification::create([
            'user_id' => $user->id,
            'type' => 'vendor_approved',
            'title' => 'You are approved! 🎉',
            'message' => 'Your vendor account has been approved. Sign in to start selling on Market Mirror.',
            'read' => false,
            'link' => '/vendor/dashboard',
        ]);

        $this->sendApprovalEmail($user, $shop->name ?? null);

        return $user;
    }

    public function rejectVendor(int $id): User
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'suspended']);
        $user->vendorApplications()->latest('id')->first()?->update(['status' => 'Rejected']);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'vendor_rejected',
            'title' => 'Application not approved',
            'message' => 'Your vendor application was not approved at this time. Contact support if you believe this is a mistake.',
            'read' => false,
            'link' => '/vendor/registration',
        ]);

        return $user;
    }

    private function sendApprovalEmail(User $user, ?string $shopName): void
    {
        try {
            Mail::to($user->email)->send(new VendorApprovedMail($user, $shopName));
        } catch (\Throwable $e) {
            Log::warning('Failed to send vendor approval email to '.$user->email.': '.$e->getMessage());
        }
    }

    public function setActiveMarket(User $user, ?int $marketId): User
    {
        $user->update(['active_market_id' => $marketId]);

        return $user->load('activeMarket');
    }

    public function activeMarket(User $user): ?Market
    {
        $user->load('activeMarket');

        return $user->activeMarket;
    }
}
