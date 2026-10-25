<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Http\Resources;

use App\Modules\Users\Application\DTOs\UserData;
use App\Modules\Users\Infrastructure\Models\User;

final class UserProfileResource
{
    public static function fromUserData(UserData $user, ?string $createdAt = null): array
    {
        return [
            'uuid' => $user->uuid,
            'email' => $user->email,
            'email_verified' => $user->emailVerifiedAt !== null,
            'display_name' => $user->displayName,
            'preferred_locale' => $user->preferredLocale,
            'roles' => $user->roles,
            'created_at' => $createdAt,
        ];
    }

    public static function fromModel(User $user): array
    {
        return [
            'uuid' => $user->uuid,
            'email' => $user->email,
            'email_verified' => $user->hasVerifiedEmail(),
            'display_name' => $user->display_name ?? $user->name,
            'preferred_locale' => $user->preferredLanguage?->code,
            'roles' => $user->roles()->pluck('code')->values()->all(),
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}
