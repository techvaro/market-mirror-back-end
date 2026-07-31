<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BuildingService;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    public function __construct(
        private readonly BuildingService $buildingService
    ) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->buildingService->list(),
        ]);
    }
}
