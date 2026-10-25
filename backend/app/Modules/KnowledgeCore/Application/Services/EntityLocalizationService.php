<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Services;

use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityLocalization;
use App\Modules\Users\Infrastructure\Models\Language;

final class EntityLocalizationService
{
    public function __construct(
        private readonly EntitySlugValidator $slugValidator,
    ) {}

    public function upsertLocalization(
        int $entityId,
        string $locale,
        string $title,
        ?string $slug = null,
        ?string $summary = null,
    ): EntityLocalization {
        $languageId = Language::query()
            ->where('code', $locale)
            ->value('id');

        if ($languageId === null) {
            throw new \InvalidArgumentException('Unsupported locale.');
        }

        $existing = EntityLocalization::query()
            ->where('entity_id', $entityId)
            ->where('language_id', $languageId)
            ->first();

        $resolvedSlug = $this->slugValidator->resolveSlug(
            title: $title,
            slug: $slug ?? $existing?->slug,
            languageId: (int) $languageId,
            excludeLocalizationId: $existing?->id,
        );

        return EntityLocalization::query()->updateOrCreate(
            [
                'entity_id' => $entityId,
                'language_id' => $languageId,
            ],
            [
                'title' => $title,
                'slug' => $resolvedSlug,
                'summary' => $summary,
                'translation_status' => $existing?->translation_status ?? 'draft',
                'workflow_state' => $existing?->workflow_state ?? WorkflowState::Draft->value,
            ],
        );
    }

    public function getByEntityAndLocale(int $entityId, string $locale): ?EntityLocalization
    {
        $languageId = Language::query()
            ->where('code', $locale)
            ->value('id');

        if ($languageId === null) {
            return null;
        }

        return EntityLocalization::query()
            ->where('entity_id', $entityId)
            ->where('language_id', $languageId)
            ->first();
    }

    /**
     * @return list<EntityLocalization>
     */
    public function listForEntity(int $entityId): array
    {
        return EntityLocalization::query()
            ->where('entity_id', $entityId)
            ->orderBy('language_id')
            ->get()
            ->all();
    }
}
