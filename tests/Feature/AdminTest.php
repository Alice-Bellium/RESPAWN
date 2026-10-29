<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_reject_guests_and_buyers(): void
    {
        $this->getJson('/api/admin/products')->assertUnauthorized();
        $this
            ->actingAs(User::factory()->create())
            ->getJson('/api/admin/products')
            ->assertForbidden();
    }

    public function test_admin_can_publish_edit_and_archive_a_product(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        $product = Product::factory()->create();
        $data = $product->toArray();
        $data['brand'] = 'RESPAWN';
        $data['kind'] = 'keyboard';
        $data['is_featured'] = true;
        $data['price'] = 55500;
        $this
            ->actingAs($admin)
            ->putJson('/api/admin/products/' . $product->id, $data)
            ->assertSuccessful();
        $this->assertSame(55500, $product->fresh()->price);
        $badSpecifications = $data;
        $badSpecifications['specifications'] = [
            str_repeat('x', 101) => 'value',
        ];
        $this->putJson('/api/admin/products/' . $product->id, $badSpecifications)->assertUnprocessable();
        $data['price'] = -1;
        $this->putJson('/api/admin/products/' . $product->id, $data)->assertUnprocessable();
        $this->deleteJson('/api/admin/products/' . $product->id)->assertSuccessful();
        $this->assertFalse($product->fresh()->is_active);
        $this
            ->getJson('/api/admin/summary')
            ->assertOk()
            ->assertJsonPath('products', 1);
    }

    public function test_fulfillment_cannot_go_backwards(): void
    {
        $order = Order::factory()->create();
        $this
            ->actingAs(User::factory()->create([
                'is_admin' => true,
            ]))
            ->patchJson('/api/admin/orders/' . $order->id, [
                'status' => 'shipped',
            ])
            ->assertSuccessful();
        $this->patchJson('/api/admin/orders/' . $order->id, [
            'status' => 'processing',
        ])->assertUnprocessable();
        $this->patchJson('/api/admin/orders/' . $order->id, [
            'status' => 'completed',
        ])->assertSuccessful();
    }
}
