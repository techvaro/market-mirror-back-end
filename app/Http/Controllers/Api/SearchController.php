<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ShopResource;
use App\Http\Resources\VendorApplicationResource;
use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        private readonly SearchService $searchService
    ) {}

    public function index(Request $request)
    {
        $results = $this->searchService->search(
            $request->get('q', ''),
            $request->has('lat') ? (float) $request->get('lat') : null,
            $request->has('lng') ? (float) $request->get('lng') : null,
            $request->has('radius') ? (float) $request->get('radius') : null,
        );

        return response()->json([
            'success' => true,
            'data' => [
                'shops' => ShopResource::collection($results['shops']),
                'products' => ProductResource::collection($results['products']),
                'vendors' => VendorApplicationResource::collection($results['vendors']),
            ],
        ]);
    }
}
