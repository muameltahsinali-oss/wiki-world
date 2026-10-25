<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\DTOs;

final readonly class SaveEntityDraftData
{
    /**
     * @param  array<string, mixed>|null  $profile
     * @param  list<array<string, mixed>>|null  $blocks
     */
    public function __construct(
        public string $uuid,
        public string $locale,
        public ?string $title = null,
        public ?string $slug = null,
        public ?string $summary = null,
        public ?array $profile = null,
        public ?array $blocks = null,
    ) {}
}
