<?php

declare(strict_types=1);

namespace App\Modules\RolesPermissions\Application\Services;

use App\Modules\RolesPermissions\Domain\Enums\Role;
use App\Modules\Users\Infrastructure\Models\User;

final class RoleResolver
{
    /**
     * @return list<string>
     */
    public function rolesForUser(User $user): array
    {
        return $user->roles()
            ->orderBy('id')
            ->pluck('code')
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function tokenAbilitiesForUser(User $user): array
    {
        $roles = $this->rolesForUser($user);
        $abilities = ['read'];

        if ($roles !== []) {
            $abilities[] = 'account';
        }

        if ($this->hasAnyRole($roles, [
            Role::Editor->value,
            Role::Reviewer->value,
            Role::SeniorEditor->value,
            Role::Admin->value,
            Role::SuperAdmin->value,
        ])) {
            $abilities[] = 'editorial';
        }

        if ($this->hasAnyRole($roles, [
            Role::Admin->value,
            Role::SuperAdmin->value,
        ])) {
            $abilities[] = 'admin';
        }

        return array_values(array_unique($abilities));
    }

    /**
     * @param  list<string>  $roles
     * @param  list<string>  $needles
     */
    private function hasAnyRole(array $roles, array $needles): bool
    {
        return array_intersect($roles, $needles) !== [];
    }
}
