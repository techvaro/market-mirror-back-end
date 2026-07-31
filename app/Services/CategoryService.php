<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public function list()
    {
        return Category::where('active', true)->get();
    }
}
