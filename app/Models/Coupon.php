<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'title',
        'description',
        'discount_type',
        'discount_value',
        'valid_until',
        'usage_limit',
        'usage_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'valid_until' => 'datetime',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
