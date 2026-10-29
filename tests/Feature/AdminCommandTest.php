<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_command_does_not_promote_or_overwrite_existing_accounts(): void
    {
        $buyer = User::factory()->create([
            'email' => 'existing@example.test',
        ]);
        $hash = $buyer->password;
        $this->artisan('shop:create-admin', [
            'email' => $buyer->email,
            '--password' => 'NewPassword123!',
        ])
            ->assertFailed();
        $this->assertFalse($buyer->fresh()->is_admin);
        $this->assertSame($hash, $buyer->fresh()->password);
    }

    public function test_demo_accounts_are_forbidden_outside_local_and_testing(): void
    {
        $this->app->instance('env', 'production');
        $this->artisan('shop:demo-accounts')->assertFailed();
        $this->assertDatabaseCount('users', 0);
    }
}
