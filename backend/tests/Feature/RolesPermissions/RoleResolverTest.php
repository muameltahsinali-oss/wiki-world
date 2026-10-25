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

final class RoleResolverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, RolePermissionSeeder::class]);
    }

    public function test_token_abilities_include_editorial_for_editor(): void
    {
        $repository = $this->app->make(UserRepositoryInterface::class);
        $user = $repository->create(new CreateUserData(
            email: 'abilities@wikiworld.test',
            password: 'SecurePass123!',
        ));
        $repository->assignRole($user->uuid, Role::Editor->value);

        $model = User::query()->where('uuid', $user->uuid)->firstOrFail();
        $resolver = $this->app->make(RoleResolver::class);

        $abilities = $resolver->tokenAbilitiesForUser($model);

        $this->assertContains('read', $abilities);
        $this->assertContains('account', $abilities);
        $this->assertContains('editorial', $abilities);
        $this->assertNotContains('admin', $abilities);
    }

    public function test_token_abilities_include_admin_for_super_admin(): void
    {
        $repository = $this->app->make(UserRepositoryInterface::class);
        $user = $repository->create(new CreateUserData(
            email: 'super@wikiworld.test',
            password: 'SecurePass123!',
        ));
        $repository->assignRole($user->uuid, Role::SuperAdmin->value);

        $model = User::query()->where('uuid', $user->uuid)->firstOrFail();
        $abilities = $this->app->make(RoleResolver::class)->tokenAbilitiesForUser($model);

        $this->assertContains('admin', $abilities);
        $this->assertContains('editorial', $abilities);
    }
}
