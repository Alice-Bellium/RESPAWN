<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_authenticates_buyer_and_ignores_admin_flag(): void
    {
        $this
            ->postJson('/api/register', [
                'name' => 'Mary',
                'email' => 'MARY@example.com',
                'password' => 'MyPassword123',
                'password_confirmation' => 'MyPassword123',
                'is_admin' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('user.email', 'mary@example.com')
            ->assertJsonPath('user.is_admin', false)
            ->assertJsonStructure([
                'csrf_token',
            ]);
        $this->assertAuthenticated();
        $this->assertTrue(Hash::check('MyPassword123', User::first()->password));
        $this
            ->getJson('/api/session')
            ->assertOk()
            ->assertJsonMissingPath('user.password');
    }

    public function test_login_and_logout(): void
    {
        $user = User::factory()->create([
            'password' => 'MyPassword123',
        ]);
        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong',
        ])->assertUnprocessable();
        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'MyPassword123',
        ])->assertOk();
        $this->assertAuthenticatedAs($user);
        $this
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('user', null);
        $this->assertGuest();
    }

    public function test_profile_and_password_require_authentication_and_current_password(): void
    {
        $this->patchJson('/api/profile', [
            'name' => 'Mary',
        ])->assertUnauthorized();
        $user = User::factory()->create([
            'password' => 'MyPassword123',
        ]);
        $this
            ->actingAs($user)
            ->patchJson('/api/profile', [
                'name' => 'Mary',
                'is_admin' => true,
            ])
            ->assertOk()
            ->assertJsonPath('user.is_admin', false);
        $this
            ->putJson(
                '/api/password',
                [
                    'current_password' => 'wrong',
                    'password' => 'NewPassword456',
                    'password_confirmation' => 'NewPassword456',
                ]
            )
            ->assertUnprocessable();
        $this
            ->putJson(
                '/api/password',
                [
                    'current_password' => 'MyPassword123',
                    'password' => 'NewPassword456',
                    'password_confirmation' => 'NewPassword456',
                ]
            )
            ->assertOk();
        $this->assertTrue(Hash::check('NewPassword456', $user->fresh()->password));
    }
}
