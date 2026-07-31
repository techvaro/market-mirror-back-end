<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        private readonly SearchService $searchService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->searchService->search($request->get('q', '')),
        ]);
    }
}
