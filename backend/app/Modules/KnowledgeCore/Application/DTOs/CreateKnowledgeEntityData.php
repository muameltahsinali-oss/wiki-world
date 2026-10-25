<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\DTOs;

final readonly class CreateKnowledgeEntityData
{
    /**
     * @param  array<string, mixed>|null  $moduleFlags
     */
    public function __construct(
        public string $entityTypeCode,
        public ?int $createdByUserId = null,
        public ?array $moduleFlags = null,
        public ?bool $isIndexable = null,
    ) {}
}
