<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationQueue extends Model
{
    use HasFactory;

    protected $table = 'verification_queue';

    protected $fillable = [
        'vendor_name',
        'market',
        'mapper_id',
        'mapper_name',
        'latitude',
        'longitude',
        'photos',
        'documents',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'photos' => 'array',
            'documents' => 'array',
        ];
    }

    public function mapper(): BelongsTo
    {
        return $this->belongsTo(Mapper::class);
    }
}
