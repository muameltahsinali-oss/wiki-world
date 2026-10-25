<?php

declare(strict_types=1);

namespace Tests\Feature\RolesPermissions;

use App\Models\User;
use App\Modules\RolesPermissions\Application\Services\RoleResolver;
use App\Modules\RolesPermissions\Domain\Enums\Role;
use App\Modules\Users\Application\Contracts\UserRepositoryInterface;
use App\Modules\Users\Application\DTOs\CreateUserData;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuthorizationMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, RolePermissionSeeder::class]);
    }

    public function test_editor_can_access_editorial_status(): void
    {
        [, $token] = $this->verifiedUserToken(Role::Editor->value);

        $this->withToken($token)
            ->getJson('/api/v1/editorial/status')
            ->assertOk()
            ->assertJsonPath('data.status', 'editorial-ready');
    }

    public function test_registered_user_cannot_access_editorial_status(): void
    {
        [, $token] = $this->verifiedUserToken(Role::Registered->value);

        $this->withToken($token)
            ->getJson('/api/v1/editorial/status')
            ->assertForbidden()
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_admin_can_access_admin_status(): void
    {
        [, $token] = $this->verifiedUserToken(Role::Admin->value);

        $this->withToken($token)
            ->getJson('/api/v1/admin/status')
            ->assertOk()
            ->assertJsonPath('data.status', 'admin-ready');
    }

    public function test_editor_cannot_access_admin_status(): void
    {
        [, $token] = $this->verifiedUserToken(Role::Editor->value);

        $this->withToken($token)
            ->getJson('/api/v1/admin/status')
            ->assertForbidden()
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    /**
     * @return array{0: User, 1: string}
     */
    private function verifiedUserToken(string $role): array
    {
        $repository = $this->app->make(UserRepositoryInterface::class);
        $user = $repository->create(new CreateUserData(
            email: $role.'@wikiworld.test',
            password: 'SecurePass123!',
        ));
        $repository->assignRole($user->uuid, $role);

        $model = User::query()->where('uuid', $user->uuid)->firstOrFail();
        $model->markEmailAsVerified();

        $abilities = $this->app->make(RoleResolver::class)->tokenAbilitiesForUser($model);
        $token = $model->createToken('test', $abilities)->plainTextToken;

        return [$model, $token];
    }
}
