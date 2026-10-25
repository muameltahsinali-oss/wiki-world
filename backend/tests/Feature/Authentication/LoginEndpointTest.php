<?php

declare(strict_types=1);

namespace Tests\Feature\Authentication;

use App\Models\User;
use App\Modules\RolesPermissions\Domain\Enums\Role;
use App\Modules\Users\Application\Contracts\UserRepositoryInterface;
use App\Modules\Users\Application\DTOs\CreateUserData;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class LoginEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, RolePermissionSeeder::class]);
    }

    public function test_login_returns_sanctum_token_and_user_summary(): void
    {
        $repository = $this->app->make(UserRepositoryInterface::class);
        $user = $repository->create(new CreateUserData(
            email: 'login@wikiworld.test',
            password: 'SecurePass123!',
        ));
        $repository->assignRole($user->uuid, Role::Registered->value);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@wikiworld.test',
            'password' => 'SecurePass123!',
            'device_name' => 'test-device',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.email', 'login@wikiworld.test')
            ->assertJsonPath('data.user.roles', ['registered'])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'expires_at',
                    'user' => ['uuid', 'email', 'roles'],
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'test-device',
        ]);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'user@wikiworld.test',
            'password_hash' => Hash::make('SecurePass123!'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@wikiworld.test',
            'password' => 'WrongPassword123!',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
    }

    public function test_login_rejects_unknown_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'missing@wikiworld.test',
            'password' => 'SecurePass123!',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
    }

    public function test_login_rejects_inactive_users(): void
    {
        User::factory()->create([
            'email' => 'inactive@wikiworld.test',
            'password_hash' => Hash::make('SecurePass123!'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive@wikiworld.test',
            'password' => 'SecurePass123!',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
    }
}
