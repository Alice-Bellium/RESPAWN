<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function payload(Product $product, int $quantity = 2): array
    {
        return [
            'idempotency_key' => (string)Str::uuid(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                ],
            ],
            'customer_name' => 'Test Buyer',
            'email' => 'buyer@example.test',
            'phone' => '+79990000000',
            'delivery_method' => 'delivery',
            'address' => 'Test street, 1',
        ];
    }

    public function test_checkout_uses_server_prices_and_is_idempotent(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 100000,
            'stock' => 5,
        ]);
        $input = $this->payload($product);
        $input['total'] = 1;
        $input['items'][0]['unit_price'] = 1;
        $first = $this
            ->actingAs($buyer)
            ->postJson('/api/orders', $input)
            ->assertSuccessful()
            ->assertJsonPath('data.total', 249000);
        $this
            ->postJson('/api/orders', $input)
            ->assertSuccessful()
            ->assertJsonPath('data.number', $first->json('data.number'));
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(1, Order::count());
        $input['items'][0]['quantity'] = 1;
        $this->postJson('/api/orders', $input)->assertStatus(409);
    }

    public function test_insufficient_or_hidden_stock_never_creates_partial_orders(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create([
            'stock' => 1,
        ]);
        $other = Product::factory()->create([
            'stock' => 5,
        ]);
        $payload = $this->payload($product);
        $payload['items'][] = [
            'product_id' => $other->id,
            'quantity' => 1,
        ];
        $this
            ->actingAs($buyer)
            ->postJson('/api/orders', $payload)
            ->assertUnprocessable();
        $this->assertSame(5, $other->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
        $product->update([
            'is_active' => false,
        ]);
        $this->postJson('/api/orders', $this->payload($product, 1))->assertUnprocessable();
    }

    public function test_orders_are_private_and_cancel_returns_stock_only_once(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create([
            'stock' => 3,
        ]);
        $order = app(CheckoutService::class)->place($buyer, $this->payload($product, 1));
        $this
            ->actingAs(User::factory()->create())
            ->getJson('/api/orders/' . $order->number)
            ->assertNotFound();
        $this->postJson('/api/orders/' . $order->number . '/cancel')->assertNotFound();
        $this
            ->actingAs($buyer)
            ->postJson('/api/orders/' . $order->number . '/cancel')
            ->assertSuccessful();
        $this->postJson('/api/orders/' . $order->number . '/cancel')->assertSuccessful();
        $this->assertSame(3, $product->fresh()->stock);
        $this->getJson('/api/orders')->assertJsonCount(1, 'data');
    }

    public function test_shipping_and_validation_and_fulfilled_orders(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 1500000,
            'stock' => 3,
        ]);
        $this->postJson('/api/orders', $this->payload($product, 1))->assertUnauthorized();
        $order = app(CheckoutService::class)->place($buyer, $this->payload($product, 1));
        $this->assertSame(0, $order->delivery_total);
        app(CheckoutService::class)->transition($order, Order::SHIPPED);
        $this
            ->actingAs($buyer)
            ->postJson('/api/orders/' . $order->number . '/cancel')
            ->assertUnprocessable();
        $bad = $this->payload($product, 0);
        $this
            ->postJson('/api/orders', $bad)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('items.0.quantity');
    }
}
