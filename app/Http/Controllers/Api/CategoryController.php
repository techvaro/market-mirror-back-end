<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->categoryService->list(),
        ]);
    }
}
