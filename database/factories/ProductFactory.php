<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Случайные товары для тестов и наполнения базы при разработке.
 *
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /** @var class-string<Product> */
    protected $model = Product::class;

    /**
     * Опубликованный товар в наличии с ценой в копейках.
     *
     * Если категория не передана через for(), фабрика создаст её сама.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'RESPAWN ' . strtoupper(fake()->bothify('??-###'));

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->uuid(),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(99_000, 24_999_000),
            'stock' => fake()->numberBetween(1, 30),
            'is_active' => true,
        ];
    }

    /**
     * Неопубликованный товар для проверки скрытых позиций каталога.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Товар с нулевым остатком. Статус публикации сохраняется.
     */
    public function outOfStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'stock' => 0,
        ]);
    }
}
