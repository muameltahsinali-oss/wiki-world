<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Http\Resources;

use App\Modules\Users\Application\DTOs\UserData;

final class UserSummaryResource
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(UserData $user): array
    {
        return [
            'uuid' => $user->uuid,
            'email' => $user->email,
            'display_name' => $user->displayName,
            'preferred_locale' => $user->preferredLocale,
            'roles' => $user->roles,
        ];
    }
}
