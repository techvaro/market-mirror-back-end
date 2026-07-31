<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'name',
        'description',
        'category',
        'price',
        'stock',
        'rating',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'boolean',
            'rating' => 'decimal:1',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
