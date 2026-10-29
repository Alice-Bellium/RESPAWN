<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'name' => 'RESPAWN Pulse',
            'kind' => 'keyboard',
            'sku' => 'RSP-TEST',
            'quantity' => 1,
            'unit_price' => 100000,
            'total' => 100000,
        ];
    }
}
