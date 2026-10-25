<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Application\DTOs;

use App\Modules\Users\Application\DTOs\UserData;

final readonly class LoginResult
{
    public function __construct(
        public string $token,
        public string $tokenType,
        public ?string $expiresAt,
        public UserData $user,
    ) {}
}
