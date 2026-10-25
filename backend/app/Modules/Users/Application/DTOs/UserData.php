<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\DTOs;

final readonly class UserData
{
    /**
     * @param  list<string>  $roles
     */
    public function __construct(
        public string $uuid,
        public string $email,
        public ?string $displayName,
        public ?string $preferredLocale,
        public bool $isActive,
        public bool $mfaEnabled,
        public ?string $emailVerifiedAt,
        public array $roles = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'email' => $this->email,
            'display_name' => $this->displayName,
            'preferred_locale' => $this->preferredLocale,
            'is_active' => $this->isActive,
            'mfa_enabled' => $this->mfaEnabled,
            'email_verified_at' => $this->emailVerifiedAt,
            'roles' => $this->roles,
        ];
    }
}
