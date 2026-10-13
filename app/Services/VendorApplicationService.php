<?php

namespace App\Services;

use App\Models\Market;
use App\Models\Shop;
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
        if (isset($data['status'])) {
            $data['status'] = match (strtolower($data['status'])) {
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                default => 'Pending Review',
            };
        }
        $application->update($data);

        if ($application->status === 'Approved') {
            $application->user()->update(['status' => 'active']);
            $this->ensureShopExists($application);
        }

        return $application;
    }

    public function ensureShopExists(VendorApplication $application): void
    {
        if (Shop::where('user_id', $application->user_id)->exists()) {
            return;
        }

        $market = Market::where('name', $application->market)->first();

        Shop::create([
            'user_id' => $application->user_id,
            'market_id' => $market?->id,
            'name' => $application->shop_name,
            'category' => $application->category,
            'description' => $application->description ?? '',
            'location' => trim(implode(', ', array_filter([
                $application->building_name,
                $application->shop_address,
            ]))),
            'market' => $application->market,
            'city' => $market?->city ?? $application->market,
            'phone' => $application->phone,
            'shop_number' => $application->shop_number,
        ]);
    }
}
