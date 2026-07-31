<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'mapper_id',
        'vendor_name',
        'market',
        'street',
        'shop_block',
        'latitude',
        'longitude',
        'accuracy',
        'status',
        'mapper_name',
        'photos',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'photos' => 'array',
        ];
    }

    public function mapper(): BelongsTo
    {
        return $this->belongsTo(Mapper::class);
    }
}
