<?php

namespace App\Services;

use App\Models\Market;

class MarketService
{
    public function list(int $perPage = 15)
    {
        return Market::where('is_active', true)->orderBy('name')->paginate($perPage);
    }

    public function find(int $id)
    {
        return Market::with('state')->findOrFail($id);
    }

    public function adminList(int $perPage = 15)
    {
        return Market::with('state')->latest()->paginate($perPage);
    }

    public function create(array $data): Market
    {
        return Market::create($data);
    }

    public function update(int $id, array $data): Market
    {
        $market = Market::findOrFail($id);
        $market->update($data);

        return $market->load('state');
    }

    public function setActive(int $id, bool $active): Market
    {
        $market = Market::findOrFail($id);
        $market->update(['is_active' => $active]);

        return $market;
    }

    public function delete(int $id): void
    {
        Market::findOrFail($id)->delete();
    }
}
