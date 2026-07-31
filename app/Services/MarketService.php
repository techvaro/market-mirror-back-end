<?php

namespace App\Services;

use App\Models\Market;

class MarketService
{
    public function list(int $perPage = 15)
    {
        return Market::latest()->paginate($perPage);
    }

    public function find(int $id)
    {
        return Market::findOrFail($id);
    }
}
