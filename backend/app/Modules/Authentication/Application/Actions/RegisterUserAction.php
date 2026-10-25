<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Application\Actions;

use App\Models\User;
use App\Modules\RolesPermissions\Domain\Enums\Role;
use App\Modules\Users\Application\Contracts\UserRepositoryInterface;
use App\Modules\Users\Application\DTOs\CreateUserData;
use App\Modules\Users\Application\DTOs\UserData;

final class RegisterUserAction
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function execute(CreateUserData $data): UserData
    {
        $user = $this->users->create($data);
        $user = $this->users->assignRole($user->uuid, Role::Registered->value);

        $model = User::query()->where('uuid', $user->uuid)->first();
        $model?->sendEmailVerificationNotification();

        return $this->users->toData($user);
    }
}
