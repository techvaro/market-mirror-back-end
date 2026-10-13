<?php

namespace App\Services;

use App\Models\ShopLocation;

class ShopLocationService
{
    public function list(array $filters, int $perPage = 15)
    {
        $query = ShopLocation::with('mapper');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['market'])) {
            $query->where('market', $filters['market']);
        }
        if (isset($filters['lat'], $filters['lng'], $filters['radius'])) {
            $latRad = deg2rad((float) $filters['lat']);
            $lngRad = deg2rad((float) $filters['lng']);

            $query->whereRaw(
                '(
                    6371 * acos(
                        cos(?) * cos(radians(latitude)) *
                        cos(radians(longitude) - ?) +
                        sin(?) * sin(radians(latitude))
                    )
                ) <= ?',
                [$latRad, $lngRad, $latRad, (float) $filters['radius']]
            );
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data, ?object $mapperProfile): ShopLocation
    {
        return ShopLocation::create([
            ...$data,
            'mapper_id' => $mapperProfile?->id,
            'status' => 'pending',
        ]);
    }

    public function find(int $id): ShopLocation
    {
        return ShopLocation::with('mapper')->findOrFail($id);
    }

    public function update(int $id, array $data): ShopLocation
    {
        $location = ShopLocation::findOrFail($id);
        $location->update($data);

        return $location;
    }
}
