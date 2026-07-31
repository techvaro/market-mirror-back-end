<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Service::with('shop');

        if ($request->has('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $services = $query->get();

        return response()->json($services);
    }
}
