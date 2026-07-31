<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Building;
use Illuminate\Http\JsonResponse;

class BuildingController extends Controller
{
    public function index(): JsonResponse
    {
        $buildings = Building::orderBy('name')->get();

        return response()->json($buildings);
    }

    public function show(Building $building): JsonResponse
    {
        return response()->json($building);
    }
}
