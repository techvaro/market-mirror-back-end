<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'name',
        'original_price',
        'sale_price',
        'discount_percentage',
        'start_time',
        'end_time',
        'sold_count',
        'stock_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'original_price' => 'integer',
            'sale_price' => 'integer',
            'discount_percentage' => 'decimal:2',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'sold_count' => 'integer',
            'stock_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
