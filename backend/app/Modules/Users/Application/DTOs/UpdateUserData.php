<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\DTOs;

final readonly class UpdateUserData
{
    public function __construct(
        public ?string $displayName = null,
        public ?string $preferredLocale = null,
        public ?bool $isActive = null,
        public ?string $password = null,
    ) {}
}
