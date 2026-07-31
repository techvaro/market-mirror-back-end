<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'products_count',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'products_count' => 'integer',
            'active' => 'boolean',
        ];
    }
}
