<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'city',
        'state',
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
