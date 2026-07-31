<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'shop_count',
        'category',
        'banner_gradient',
    ];

    protected function casts(): array
    {
        return [
            'shop_count' => 'integer',
        ];
    }
}
