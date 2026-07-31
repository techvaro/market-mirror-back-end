<?php

namespace App\Services;

use App\Models\Building;

class BuildingService
{
    public function list()
    {
        return Building::all();
    }
}
