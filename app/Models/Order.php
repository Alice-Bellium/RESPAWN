<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A checkout snapshot. All monetary amounts are integer kopeks. */
class Order extends Model
{
    use HasFactory;

    public const PROCESSING = 'processing';

    public const SHIPPED = 'shipped';

    public const COMPLETED = 'completed';

    public const CANCELLED = 'cancelled';

    protected $guarded = [
        'id',
    ];

    /** Type stable totals and timestamps. */
    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'delivery_total' => 'integer',
            'total' => 'integer',
            'cancelled_at' => 'datetime',
        ];
    }

    /** The buyer who owns this order. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Original product descriptions and prices survive catalog changes. */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
