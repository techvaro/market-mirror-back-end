<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'mapper_id',
        'vendor_id',
        'title',
        'description',
        'type',
        'market',
        'street',
        'shop_block',
        'priority',
        'status',
        'daily_target',
        'due_date',
        'assigned_at',
        'completed_at',
        'corrections',
    ];

    protected function casts(): array
    {
        return [
            'daily_target' => 'integer',
            'due_date' => 'datetime',
            'assigned_at' => 'datetime',
            'completed_at' => 'datetime',
            'corrections' => 'array',
        ];
    }

    public function mapper(): BelongsTo
    {
        return $this->belongsTo(Mapper::class);
    }
}
