<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An immutable product and price snapshot belonging to an order. */
class OrderItem extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    /** Keep calculations in integer kopeks. */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'total' => 'integer',
        ];
    }

    /** The order containing this snapshot. */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** The current product, if it still exists. */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
