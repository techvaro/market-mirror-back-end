<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'delivery_method',
        'payment_method',
        'subtotal',
        'delivery_fee',
        'total',
        'estimated_delivery',
        'cancellation_reason',
        'address_first_name',
        'address_last_name',
        'address_phone',
        'address_street',
        'address_state',
        'address_city',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'delivery_fee' => 'integer',
            'total' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }
}
