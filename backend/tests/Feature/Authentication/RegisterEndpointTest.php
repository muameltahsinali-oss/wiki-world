<?php

declare(strict_types=1);

namespace Tests\Feature\Authentication;

use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RegisterEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, RolePermissionSeeder::class]);
    }

    public function test_register_creates_user_with_registered_role(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'newuser@wikiworld.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'preferred_locale' => 'ar',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.message', 'verification_sent')
            ->assertJsonPath('data.user.email', 'newuser@wikiworld.test')
            ->assertJsonPath('data.user.preferred_locale', 'ar')
            ->assertJsonPath('data.user.roles', ['registered'])
            ->assertJsonStructure([
                'data' => [
                    'user' => ['uuid', 'email', 'display_name', 'preferred_locale', 'roles'],
                    'message',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@wikiworld.test',
        ]);
    }

    public function test_register_validates_password_complexity(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'weak@wikiworld.test',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('error.code', 'VALIDATION_FAILED');
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@wikiworld.test']);

        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'taken@wikiworld.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('error.code', 'VALIDATION_FAILED');
    }

    public function test_authenticated_users_cannot_register(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/auth/register', [
            'email' => 'another@wikiworld.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertForbidden();
    }
}
