<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\DTOs;

use App\Modules\KnowledgeCore\Domain\Entities\KnowledgeEntity;

final readonly class EntityDraftResult
{
    public function __construct(
        public KnowledgeEntity $entity,
        public string $domain,
        public string $locale,
        public string $title,
        public string $slug,
        public ?string $summary,
    ) {}
}
