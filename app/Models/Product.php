<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * Scope products to a market by joining through their shop.
     */
    public function scopeForMarket(Builder $query, ?int $marketId): Builder
    {
        return $marketId === null
            ? $query
            : $query->whereHas('shop', fn (Builder $q) => $q->where('market_id', $marketId));
    }

    protected $fillable = [
        'shop_id',
        'name',
        'sku',
        'description',
        'category',
        'price',
        'stock',
        'rating',
        'color',
        'image_url',
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

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }
}
