<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\RolesPermissions\Domain\Enums\Permission;
use App\Modules\RolesPermissions\Domain\Enums\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class RolePermissionSeeder extends Seeder
{
    /**
     * @return list<array<string, string|null>>
     */
    public static function roleDefinitions(): array
    {
        return [
            ['code' => Role::Registered->value, 'name' => 'Registered User', 'description' => 'Public registered learner'],
            ['code' => Role::Editor->value, 'name' => 'Editor', 'description' => 'Content editor'],
            ['code' => Role::Reviewer->value, 'name' => 'Reviewer', 'description' => 'Editorial reviewer'],
            ['code' => Role::SeniorEditor->value, 'name' => 'Senior Editor', 'description' => 'Senior editorial authority'],
            ['code' => Role::Admin->value, 'name' => 'Administrator', 'description' => 'Platform administrator'],
            ['code' => Role::SuperAdmin->value, 'name' => 'Super Administrator', 'description' => 'Full platform control'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function rolePermissionMap(): array
    {
        return [
            Role::Registered->value => [
                Permission::EntityViewPublished->value,
            ],
            Role::Editor->value => [
                Permission::EntityViewPublished->value,
                Permission::EntityViewDraft->value,
                Permission::EntityCreate->value,
                Permission::EntityUpdate->value,
                Permission::EntitySubmit->value,
                Permission::MediaUpload->value,
            ],
            Role::Reviewer->value => [
                Permission::EntityViewPublished->value,
                Permission::EntityViewDraft->value,
                Permission::ReviewApprove->value,
            ],
            Role::SeniorEditor->value => [
                Permission::EntityViewPublished->value,
                Permission::EntityViewDraft->value,
                Permission::EntityCreate->value,
                Permission::EntityUpdate->value,
                Permission::EntitySubmit->value,
                Permission::EntityPublish->value,
                Permission::EntityUnpublish->value,
                Permission::ReviewApprove->value,
                Permission::MediaUpload->value,
                Permission::AiFlagResolve->value,
            ],
            Role::Admin->value => [
                Permission::UserManage->value,
                Permission::SettingsManage->value,
                Permission::AnalyticsView->value,
                Permission::AuditView->value,
            ],
            Role::SuperAdmin->value => array_map(
                static fn (Permission $permission): string => $permission->value,
                Permission::cases(),
            ),
        ];
    }

    public function run(): void
    {
        DB::table('roles')->upsert(
            self::roleDefinitions(),
            ['code'],
            ['name', 'description'],
        );

        $permissionRows = array_map(
            static fn (Permission $permission): array => [
                'code' => $permission->value,
                'description' => $permission->name,
            ],
            Permission::cases(),
        );

        DB::table('permissions')->upsert(
            $permissionRows,
            ['code'],
            ['description'],
        );

        $roleIds = DB::table('roles')->pluck('id', 'code');
        $permissionIds = DB::table('permissions')->pluck('id', 'code');

        $pivotRows = [];

        foreach (self::rolePermissionMap() as $roleCode => $permissionCodes) {
            foreach ($permissionCodes as $permissionCode) {
                $pivotRows[] = [
                    'role_id' => $roleIds[$roleCode],
                    'permission_id' => $permissionIds[$permissionCode],
                ];
            }
        }

        DB::table('role_permissions')->upsert(
            $pivotRows,
            ['role_id', 'permission_id'],
            ['role_id', 'permission_id'],
        );
    }
}
