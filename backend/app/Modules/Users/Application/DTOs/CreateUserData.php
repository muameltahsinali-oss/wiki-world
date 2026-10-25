<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\DTOs;

final readonly class CreateUserData
{
    public function __construct(
        public string $email,
        public string $password,
        public ?string $displayName = null,
        public ?string $preferredLocale = null,
    ) {}
}
