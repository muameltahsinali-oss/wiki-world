<?php

declare(strict_types=1);

namespace App\Modules\RolesPermissions\Infrastructure\Services;

use App\Modules\RolesPermissions\Application\Contracts\AuthorizationServiceInterface;
use App\Modules\RolesPermissions\Application\Services\RoleResolver;
use App\Modules\RolesPermissions\Domain\Enums\Permission;
use App\Modules\RolesPermissions\Domain\Enums\Role;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Support\Facades\DB;

final class AuthorizationService implements AuthorizationServiceInterface
{
    public function __construct(
        private readonly RoleResolver $roleResolver,
    ) {}

    public function userHasRole(User $user, string $role): bool
    {
        return in_array($role, $this->roleResolver->rolesForUser($user), true);
    }

    public function userHasPermission(User $user, string $permission): bool
    {
        return DB::table('user_roles')
            ->join('role_permissions', 'user_roles.role_id', '=', 'role_permissions.role_id')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('user_roles.user_id', $user->id)
            ->where('permissions.code', $permission)
            ->exists();
    }

    public function userHasDomainScope(User $user, string $domain): bool
    {
        return DB::table('editor_domain_scopes')
            ->where('user_id', $user->id)
            ->where('domain_code', $domain)
            ->exists();
    }

    public function userCanCreateInDomain(User $user, string $domain): bool
    {
        if ($this->userHasRole($user, Role::SuperAdmin->value)
            || $this->userHasRole($user, Role::SeniorEditor->value)) {
            return true;
        }

        if (! $this->userHasPermission($user, Permission::EntityCreate->value)) {
            return false;
        }

        return $this->userHasDomainScope($user, $domain);
    }

    public function userCanViewDraftInDomain(User $user, string $domain): bool
    {
        if ($this->userHasRole($user, Role::SuperAdmin->value)
            || $this->userHasRole($user, Role::SeniorEditor->value)) {
            return true;
        }

        if (! $this->userHasPermission($user, Permission::EntityViewDraft->value)) {
            return false;
        }

        return $this->userHasDomainScope($user, $domain);
    }

    public function userCanEditInDomain(User $user, string $domain): bool
    {
        if ($this->userHasRole($user, Role::SuperAdmin->value)
            || $this->userHasRole($user, Role::SeniorEditor->value)) {
            return true;
        }

        if (! $this->userHasPermission($user, Permission::EntityUpdate->value)) {
            return false;
        }

        return $this->userHasDomainScope($user, $domain);
    }
}
