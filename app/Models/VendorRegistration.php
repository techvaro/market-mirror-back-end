<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_name',
        'business_category',
        'market',
        'mapper_id',
        'mapper_name',
        'current_status',
        'approval_status',
        'photos',
        'latitude',
        'longitude',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function mapper(): BelongsTo
    {
        return $this->belongsTo(Mapper::class);
    }
}
