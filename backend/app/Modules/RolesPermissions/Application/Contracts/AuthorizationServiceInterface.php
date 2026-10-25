<?php

declare(strict_types=1);

namespace App\Modules\RolesPermissions\Application\Contracts;

use App\Modules\Users\Infrastructure\Models\User;

interface AuthorizationServiceInterface
{
    public function userHasRole(User $user, string $role): bool;

    public function userHasPermission(User $user, string $permission): bool;

    public function userHasDomainScope(User $user, string $domain): bool;

    public function userCanCreateInDomain(User $user, string $domain): bool;

    public function userCanViewDraftInDomain(User $user, string $domain): bool;

    public function userCanEditInDomain(User $user, string $domain): bool;
}
