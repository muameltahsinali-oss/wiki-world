<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Application\Actions;

use Laravel\Sanctum\PersonalAccessToken;

final class LogoutUserAction
{
    public function execute(?string $plainTextToken): void
    {
        if ($plainTextToken === null) {
            return;
        }

        PersonalAccessToken::findToken($plainTextToken)?->delete();
    }
}
