<?php

declare(strict_types=1);

namespace Tests\Feature\Authentication;

use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

final class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, RolePermissionSeeder::class]);
    }

    public function test_forgot_password_accepts_known_email(): void
    {
        User::factory()->create(['email' => 'reset@wikiworld.test']);

        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'reset@wikiworld.test',
        ])
            ->assertOk()
            ->assertJsonPath('data.message', 'reset_link_sent');
    }

    public function test_forgot_password_does_not_reveal_unknown_email(): void
    {
        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'missing@wikiworld.test',
        ])
            ->assertOk()
            ->assertJsonPath('data.message', 'reset_link_sent');
    }

    public function test_reset_password_updates_hash_and_revokes_tokens(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@wikiworld.test',
            'password_hash' => Hash::make('OldSecurePass123!'),
        ]);
        $token = $user->createToken('device')->plainTextToken;
        $resetToken = Password::createToken($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'reset@wikiworld.test',
            'password' => 'NewSecurePass123!',
            'password_confirmation' => 'NewSecurePass123!',
            'token' => $resetToken,
        ])
            ->assertOk()
            ->assertJsonPath('data.message', 'password_reset');

        $this->assertTrue(Hash::check('NewSecurePass123!', $user->fresh()->password_hash));

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertUnauthorized();
    }

    public function test_reset_password_rejects_invalid_token(): void
    {
        User::factory()->create(['email' => 'reset@wikiworld.test']);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'reset@wikiworld.test',
            'password' => 'NewSecurePass123!',
            'password_confirmation' => 'NewSecurePass123!',
            'token' => 'invalid-token',
        ])
            ->assertStatus(400)
            ->assertJsonPath('error.code', 'INVALID_RESET_TOKEN');
    }
}
