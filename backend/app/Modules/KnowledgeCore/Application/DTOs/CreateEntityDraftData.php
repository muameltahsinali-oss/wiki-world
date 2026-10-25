<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\DTOs;

final readonly class CreateEntityDraftData
{
    public function __construct(
        public string $entityTypeCode,
        public string $domain,
        public string $locale,
        public string $title,
        public ?string $slug,
        public ?string $summary,
        public int $createdByUserId,
    ) {}
}
