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
use Tests\TestCase;

final class MeEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, RolePermissionSeeder::class]);
    }

    public function test_me_returns_user_profile(): void
    {
        $repository = $this->app->make(UserRepositoryInterface::class);
        $user = $repository->create(new CreateUserData(
            email: 'me@wikiworld.test',
            password: 'SecurePass123!',
            preferredLocale: 'en',
        ));
        $repository->assignRole($user->uuid, Role::Registered->value);

        $model = User::query()->where('uuid', $user->uuid)->firstOrFail();
        $model->markEmailAsVerified();
        $token = $model->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'me@wikiworld.test')
            ->assertJsonPath('data.preferred_locale', 'en')
            ->assertJsonPath('data.email_verified', true)
            ->assertJsonPath('data.roles', ['registered']);
    }

    public function test_me_update_changes_display_name_and_locale(): void
    {
        $user = User::factory()->create(['display_name' => 'Old Name']);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->patchJson('/api/v1/me', [
                'display_name' => 'New Name',
                'preferred_locale' => 'en',
            ])
            ->assertOk()
            ->assertJsonPath('data.display_name', 'New Name')
            ->assertJsonPath('data.preferred_locale', 'en');
    }

    public function test_me_delete_anonymizes_account(): void
    {
        $user = User::factory()->create([
            'email' => 'delete@wikiworld.test',
            'display_name' => 'Delete Me',
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->deleteJson('/api/v1/me')
            ->assertNoContent();

        $this->assertSoftDeleted('users', ['uuid' => $user->uuid]);
        $this->assertDatabaseHas('users', [
            'uuid' => $user->uuid,
            'email' => 'deleted+'.$user->uuid.'@anonymous.wikiworld',
            'is_active' => false,
        ]);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }
}
