<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mapper extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'assigned_market',
        'assigned_region',
        'mapper_status',
        'gps_status',
        'tasks_assigned',
        'tasks_completed',
        'tasks_pending',
        'tasks_rejected',
        'approval_rate',
        'avg_completion_time_hrs',
        'distance_covered_km',
        'supervisor',
        'notes',
        'last_login_at',
    ];

    protected function casts(): array
    {
        return [
            'tasks_assigned' => 'integer',
            'tasks_completed' => 'integer',
            'tasks_pending' => 'integer',
            'tasks_rejected' => 'integer',
            'approval_rate' => 'decimal:1',
            'avg_completion_time_hrs' => 'decimal:1',
            'distance_covered_km' => 'decimal:1',
            'last_login_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function shopLocations(): HasMany
    {
        return $this->hasMany(ShopLocation::class);
    }

    public function vendorRegistrations(): HasMany
    {
        return $this->hasMany(VendorRegistration::class);
    }

    public function verificationQueue(): HasMany
    {
        return $this->hasMany(VerificationQueue::class);
    }
}
