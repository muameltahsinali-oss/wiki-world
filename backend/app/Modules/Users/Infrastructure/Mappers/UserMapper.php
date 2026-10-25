<?php

declare(strict_types=1);

namespace App\Modules\Users\Infrastructure\Mappers;

use App\Modules\Users\Application\DTOs\UserData;
use App\Modules\Users\Domain\Entities\User as UserEntity;
use App\Modules\Users\Infrastructure\Models\User as UserModel;

final class UserMapper
{
    public function toDomain(UserModel $model): UserEntity
    {
        return new UserEntity(
            id: $model->id,
            uuid: $model->uuid,
            email: $model->email,
            displayName: $model->display_name ?? $model->name,
            preferredLocale: $model->preferredLanguage?->code,
            isActive: (bool) $model->is_active,
            mfaEnabled: (bool) $model->mfa_enabled,
            emailVerifiedAt: $model->email_verified_at?->toIso8601String(),
            roles: $model->relationLoaded('roles')
                ? $model->roles->pluck('code')->values()->all()
                : [],
        );
    }

    public function toData(UserEntity $user): UserData
    {
        return new UserData(
            uuid: $user->uuid,
            email: $user->email,
            displayName: $user->displayName,
            preferredLocale: $user->preferredLocale,
            isActive: $user->isActive,
            mfaEnabled: $user->mfaEnabled,
            emailVerifiedAt: $user->emailVerifiedAt,
            roles: $user->roles,
        );
    }
}
