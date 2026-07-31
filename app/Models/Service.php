<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'name',
        'description',
        'category',
        'price',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'rating' => 'decimal:1',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
