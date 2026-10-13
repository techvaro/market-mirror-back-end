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
        'owner_name',
        'phone',
        'email',
        'business_category',
        'market',
        'shop_number',
        'mapper_id',
        'mapper_name',
        'current_status',
        'approval_status',
        'review_notes',
        'reviewed_at',
        'reviewed_by',
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
            'reviewed_at' => 'datetime',
        ];
    }

    public function mapper(): BelongsTo
    {
        return $this->belongsTo(Mapper::class);
    }
}
