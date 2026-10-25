<?php

declare(strict_types=1);

namespace Tests\Feature\RolesPermissions;

use App\Models\User;
use App\Modules\RolesPermissions\Application\Contracts\AuthorizationServiceInterface;
use App\Modules\RolesPermissions\Domain\Enums\Permission;
use App\Modules\RolesPermissions\Domain\Enums\Role;
use App\Modules\Users\Application\Contracts\UserRepositoryInterface;
use App\Modules\Users\Application\DTOs\CreateUserData;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuthorizationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, RolePermissionSeeder::class]);
    }

    public function test_user_has_role_and_permission(): void
    {
        $repository = $this->app->make(UserRepositoryInterface::class);
        $user = $repository->create(new CreateUserData(
            email: 'editor@wikiworld.test',
            password: 'SecurePass123!',
        ));
        $repository->assignRole($user->uuid, Role::Editor->value);

        $model = User::query()->where('uuid', $user->uuid)->firstOrFail();
        $authorization = $this->app->make(AuthorizationServiceInterface::class);

        $this->assertTrue($authorization->userHasRole($model, Role::Editor->value));
        $this->assertTrue($authorization->userHasPermission($model, Permission::EntityViewDraft->value));
        $this->assertFalse($authorization->userHasPermission($model, Permission::UserManage->value));
    }
}
