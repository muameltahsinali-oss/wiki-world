<?php

declare(strict_types=1);

namespace App\Modules\Publishing\Application\Services;

use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\Services\EntityLocalizationService;
use App\Modules\KnowledgeCore\Domain\Entities\KnowledgeEntity;
use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityLocalization;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityVersion;
use App\Modules\Users\Infrastructure\Models\Language;
use App\Modules\Users\Infrastructure\Models\User;

final class EntityWorkflowPersistenceService
{
    public function __construct(
        private readonly KnowledgeEntityRepositoryInterface $entities,
        private readonly EntityLocalizationService $localizations,
    ) {}

    public function persistWorkflowState(KnowledgeEntity $entity, WorkflowState $state, User $actor): KnowledgeEntity
    {
        $updated = $this->entities->save(
            $entity
                ->withWorkflowState($state)
                ->withUpdatedByUserId($actor->id),
        );

        $this->syncLocalizationWorkflowStates($entity->id(), $state);

        return $updated;
    }

    public function syncLocalizationWorkflowStates(int $entityId, WorkflowState $state): void
    {
        EntityLocalization::query()
            ->where('entity_id', $entityId)
            ->update(['workflow_state' => $state->value]);
    }

    public function resolveLatestVersionId(int $entityId): ?int
    {
        return EntityVersion::query()
            ->where('entity_id', $entityId)
            ->orderByDesc('version_number')
            ->value('id');
    }

    /**
     * @return list<string>
     */
    public function localesForEntity(int $entityId): array
    {
        $locales = [];

        foreach ($this->localizations->listForEntity($entityId) as $localization) {
            $code = Language::query()
                ->where('id', $localization->language_id)
                ->value('code');

            if (is_string($code)) {
                $locales[] = $code;
            }
        }

        return $locales;
    }
}
