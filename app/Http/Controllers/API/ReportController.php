<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request, Shop $shop): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
        ]);

        $report = Report::create([
            'user_id' => $request->user()->id,
            'shop_id' => $shop->id,
            'reason' => $validated['reason'],
            'description' => $validated['description'],
        ]);

        return response()->json($report, 201);
    }
}
