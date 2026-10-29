<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InputValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_rejects_duplicate_items_and_invalid_delivery(): void
    {
        $product = Product::factory()->create();
        $input = [
            'idempotency_key' => (string)Str::uuid(),
            'customer_name' => 'Buyer',
            'email' => 'buyer@example.test',
            'phone' => '+79990000000',
            'delivery_method' => 'delivery',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];
        $this
            ->actingAs(User::factory()->create())
            ->postJson('/api/orders', $input)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.product_id', 'address']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_server_handles_malformed_filters_without_a_server_error(): void
    {
        $this->getJson('/api/products?q[]=mouse')->assertUnprocessable();
        $this->getJson('/api/products?per_page=100000')->assertUnprocessable();
        $this->getJson('/api/products?max_price=-1')->assertUnprocessable();
        $this->getJson('/api/products?ids[]=not-a-number')->assertUnprocessable();
    }

    public function test_order_history_rejects_invalid_page_numbers(): void
    {
        $this
            ->actingAs(User::factory()->create())
            ->getJson('/api/orders?page=-1')
            ->assertUnprocessable();
        $this->getJson('/api/orders?page=10000000000000000000')->assertUnprocessable();
    }

    public function test_missing_and_inactive_products_cannot_be_ordered(): void
    {
        $input = [
            'idempotency_key' => (string)Str::uuid(),
            'customer_name' => 'Buyer',
            'email' => 'buyer@example.test',
            'phone' => '+79990000000',
            'delivery_method' => 'pickup',
            'items' => [
                [
                    'product_id' => 99999,
                    'quantity' => 1,
                ],
            ],
        ];
        $this
            ->actingAs(User::factory()->create())
            ->postJson('/api/orders', $input)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('items');
        $this->assertDatabaseCount('orders', 0);
    }
}
