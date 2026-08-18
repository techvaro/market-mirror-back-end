<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Shop extends Model
{
    use HasFactory;

    public function scopeForMarket(Builder $query, ?int $marketId): Builder
    {
        return $marketId === null ? $query : $query->where('market_id', $marketId);
    }

    /**
     * Filter shops within a radius (kilometres) of the given coordinate using
     * the Haversine formula.
     */
    public function scopeNearBy(Builder $query, float $lat, float $lng, float $radiusKm): Builder
    {
        $latRad = deg2rad($lat);
        $lngRad = deg2rad($lng);

        return $query->whereRaw(
            '(
                6371 * acos(
                    cos(?) * cos(radians(latitude)) *
                    cos(radians(longitude) - ?) +
                    sin(?) * sin(radians(latitude))
                )
            ) <= ?',
            [$latRad, $lngRad, $latRad, $radiusKm]
        );
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    protected $fillable = [
        'user_id',
        'market_id',
        'name',
        'category',
        'rating',
        'review_count',
        'product_count',
        'description',
        'location',
        'latitude',
        'longitude',
        'hours',
        'banner_gradient',
        'market',
        'city',
        'phone',
        'shop_number',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:1',
            'review_count' => 'integer',
            'product_count' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function shopImages(): HasMany
    {
        return $this->hasMany(ShopImage::class);
    }

    public function chatConversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
