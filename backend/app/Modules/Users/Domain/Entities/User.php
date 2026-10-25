<?php

declare(strict_types=1);

namespace App\Modules\Users\Domain\Entities;

final readonly class User
{
    /**
     * @param  list<string>  $roles
     */
    public function __construct(
        private int $id,
        public string $uuid,
        public string $email,
        public ?string $displayName,
        public ?string $preferredLocale,
        public bool $isActive,
        public bool $mfaEnabled,
        public ?string $emailVerifiedAt,
        public array $roles = [],
    ) {}

    public function id(): int
    {
        return $this->id;
    }
}
