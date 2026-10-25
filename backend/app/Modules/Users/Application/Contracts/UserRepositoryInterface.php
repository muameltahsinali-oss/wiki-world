<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\Contracts;

use App\Modules\Users\Application\DTOs\CreateUserData;
use App\Modules\Users\Application\DTOs\UpdateUserData;
use App\Modules\Users\Application\DTOs\UserData;
use App\Modules\Users\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function findByUuid(string $uuid): ?User;

    public function findByEmail(string $email): ?User;

    public function create(CreateUserData $data): User;

    public function update(string $uuid, UpdateUserData $data): User;

    public function delete(string $uuid): void;

    public function toData(User $user): UserData;

    public function assignRole(string $uuid, string $roleCode): User;

    public function removeRole(string $uuid, string $roleCode): User;

    /**
     * @param  list<string>  $domainCodes
     */
    public function syncDomainScopes(string $uuid, array $domainCodes): User;
}
