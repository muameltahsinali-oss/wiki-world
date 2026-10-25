<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Infrastructure\Repositories;

use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\DTOs\CreateKnowledgeEntityData;
use App\Modules\KnowledgeCore\Application\Services\EntityTypeRegistry;
use App\Modules\KnowledgeCore\Domain\Entities\KnowledgeEntity;
use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\KnowledgeCore\Domain\Support\EntityUuidGenerator;
use App\Modules\KnowledgeCore\Infrastructure\Mappers\KnowledgeEntityMapper;
use App\Modules\KnowledgeCore\Infrastructure\Models\Entity as EntityModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class EloquentKnowledgeEntityRepository implements KnowledgeEntityRepositoryInterface
{
    public function __construct(
        private readonly KnowledgeEntityMapper $mapper,
        private readonly EntityUuidGenerator $uuidGenerator,
        private readonly EntityTypeRegistry $entityTypes,
    ) {}

    public function findByUuid(string $uuid): ?KnowledgeEntity
    {
        $model = EntityModel::query()
            ->with('entityType')
            ->where('uuid', $uuid)
            ->first();

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function create(CreateKnowledgeEntityData $data): KnowledgeEntity
    {
        $typeId = $this->entityTypes->resolveId($data->entityTypeCode);

        $model = EntityModel::query()->create([
            'uuid' => $this->uuidGenerator->generateString(),
            'entity_type_id' => $typeId,
            'workflow_state' => WorkflowState::Draft->value,
            'module_flags' => $data->moduleFlags ?? [],
            'is_indexable' => $data->isIndexable ?? true,
            'created_by_user_id' => $data->createdByUserId,
            'updated_by_user_id' => $data->createdByUserId,
        ]);

        $model->load('entityType');

        return $this->mapper->toDomain($model);
    }

    public function save(KnowledgeEntity $entity): KnowledgeEntity
    {
        $model = $this->findModelOrFail($entity->uuid);

        if ($model->entity_type_id !== $entity->entityType->id()) {
            throw new \InvalidArgumentException('Entity type is immutable after creation.');
        }

        $model->update([
            'workflow_state' => $entity->workflowState->value,
            'module_flags' => $entity->moduleFlags,
            'is_indexable' => $entity->isIndexable,
            'published_at' => $entity->publishedAt,
            'published_version_id' => $entity->publishedVersionId,
            'updated_by_user_id' => $entity->updatedByUserId,
        ]);

        $model->load('entityType');

        return $this->mapper->toDomain($model->fresh());
    }

    public function delete(string $uuid): void
    {
        $this->findModelOrFail($uuid)->delete();
    }

    private function findModelOrFail(string $uuid): EntityModel
    {
        $model = EntityModel::query()->where('uuid', $uuid)->first();

        if ($model === null) {
            throw (new ModelNotFoundException)->setModel(EntityModel::class, [$uuid]);
        }

        return $model;
    }
}
