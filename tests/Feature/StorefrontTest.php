<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_spa_routes_support_refresh_but_missing_assets_do_not_return_html(): void
    {
        $this->withoutVite();
        $this
            ->get('/catalog')
            ->assertOk()
            ->assertSee('id="app"', false);
        $this->get('/orders/example')->assertOk();
        $this->getJson('/api/not-a-route')->assertNotFound();
        $this->get('/build/assets/missing.js')->assertNotFound();
        $this->get('/images/missing.svg')->assertNotFound();
    }

    public function test_demo_accounts_are_idempotent_and_never_overwrite_existing_users(): void
    {
        $this->artisan('shop:demo-accounts')->assertSuccessful();
        $this->artisan('shop:demo-accounts')->assertSuccessful();
        $this->assertSame(2, User::count());
        $this->assertTrue(User::query()->where('email', 'admin@respawn.test')->first()->is_admin);
    }
}
