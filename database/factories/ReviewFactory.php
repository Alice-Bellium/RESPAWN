<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Review> */
class ReviewFactory extends Factory
{
    /** Тестовый отзыв; данные не добавляются в публичный каталог сидером. */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'body' => fake()->paragraph(),
            'status' => Review::PENDING,
            'revision' => 1,
        ];
    }

    /** Опубликованный отзыв для проверки публичной выдачи и рейтинга. */
    public function approved(): static
    {
        return $this->state(fn() => [
            'status' => Review::APPROVED,
            'moderated_at' => now(),
        ]);
    }
}
