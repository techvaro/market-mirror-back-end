<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'rating',
        'review_count',
        'product_count',
        'description',
        'location',
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
