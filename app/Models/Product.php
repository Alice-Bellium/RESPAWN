<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Товар каталога. Принадлежит одной категории; цена хранится в копейках.
 *
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $price Цена в копейках.
 * @property int $stock Остаток товара в штуках.
 * @property bool $is_active Признак публикации товара.
 * @property string|null $sku
 * @property string $brand
 * @property string $kind
 * @property string|null $badge
 * @property bool $is_featured
 * @property array<string, string>|null $specifications
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Category|null $category
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * Поля, разрешённые для массового заполнения.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'is_active',
        'sku',
        'brand',
        'kind',
        'badge',
        'is_featured',
        'specifications',
    ];

    /**
     * Преобразования значений полей при работе с моделью.
     *
     * Цена остаётся целым числом копеек, без перевода в рубли.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'specifications' => 'array',
            'price' => 'integer',
            'stock' => 'integer',
        ];
    }

    /**
     * Категория, на которую ссылается поле category_id.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
