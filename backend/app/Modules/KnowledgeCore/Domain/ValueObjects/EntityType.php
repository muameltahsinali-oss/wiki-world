<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Domain\ValueObjects;

final readonly class EntityType
{
    public function __construct(
        private int $id,
        public string $code,
        public string $class,
        public ?string $profileTable,
        public string $primaryDomain,
        public bool $isMvp,
        public bool $isActive,
    ) {}

    public function id(): int
    {
        return $this->id;
    }
}
