<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'number' => 'RSP-' . Str::ulid(),
            'idempotency_key' => (string)Str::uuid(),
            'request_hash' => hash('sha256', 'fixture'),
            'status' => 'processing',
            'customer_name' => 'Marty',
            'email' => 'marty.' . Str::uuid() . '@example.test',
            'phone' => '+79990000000',
            'delivery_method' => 'pickup',
            'subtotal' => 100000,
            'delivery_total' => 0,
            'total' => 100000,
            'payment_method' => 'demo',
        ];
    }
}
