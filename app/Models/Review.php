<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Один отзыв подтверждённого покупателя на товар.
 *
 * @property int $rating
 * @property string $body
 * @property string $status
 * @property int $revision
 * @property string|null $moderation_note
 */
class Review extends Model
{
    use HasFactory;

    public const PENDING = 'pending';

    public const APPROVED = 'approved';

    public const REJECTED = 'rejected';

    protected $fillable = [
        'rating',
        'body',
    ];

    /** Типы оценки, версии и даты модерации. */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'revision' => 'integer',
            'moderated_at' => 'datetime',
        ];
    }

    /** Автор отзыва, чей полученный заказ проверяется перед сохранением. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Товар, к которому относится оценка. */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
