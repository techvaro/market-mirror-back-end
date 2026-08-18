<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingResource;
use App\Services\BuildingService;

class BuildingController extends Controller
{
    public function __construct(
        private readonly BuildingService $buildingService
    ) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => BuildingResource::collection($this->buildingService->list()),
        ]);
    }
}
