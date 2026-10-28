<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Случайные категории для тестов и наполнения базы при разработке.
 *
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /** @var class-string<Category> */
    protected $model = Category::class;

    /**
     * Название категории и уникальный slug.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Клавиатуры',
            'Мыши',
            'Кресла',
            'Компьютеры',
            'Мониторы',
            'Гарнитуры',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->uuid(),
        ];
    }
}
