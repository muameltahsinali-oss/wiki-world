<?php

declare(strict_types=1);

namespace Tests\Feature\RolesPermissions;

use Database\Seeders\LanguageSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RolePermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_six_mvp_roles_are_seeded(): void
    {
        $this->seed([LanguageSeeder::class, RolePermissionSeeder::class]);

        $this->assertDatabaseCount('roles', 6);
        $this->assertDatabaseHas('roles', ['code' => 'reviewer']);
        $this->assertDatabaseHas('roles', ['code' => 'senior_editor']);
    }

    public function test_permissions_are_mapped_to_roles(): void
    {
        $this->seed([LanguageSeeder::class, RolePermissionSeeder::class]);

        $this->assertGreaterThan(0, \DB::table('permissions')->count());
        $this->assertGreaterThan(0, \DB::table('role_permissions')->count());

        $reviewerRoleId = \DB::table('roles')->where('code', 'reviewer')->value('id');
        $approvePermissionId = \DB::table('permissions')->where('code', 'review.approve')->value('id');

        $this->assertDatabaseHas('role_permissions', [
            'role_id' => $reviewerRoleId,
            'permission_id' => $approvePermissionId,
        ]);
    }
}
