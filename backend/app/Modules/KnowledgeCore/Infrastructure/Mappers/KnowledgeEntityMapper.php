<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Infrastructure\Mappers;

use App\Modules\KnowledgeCore\Domain\Entities\KnowledgeEntity;
use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\KnowledgeCore\Domain\ValueObjects\EntityType;
use App\Modules\KnowledgeCore\Infrastructure\Models\Entity as EntityModel;

final class KnowledgeEntityMapper
{
    public function toDomain(EntityModel $model): KnowledgeEntity
    {
        $type = $model->entityType;

        return new KnowledgeEntity(
            id: $model->id,
            uuid: $model->uuid,
            entityType: new EntityType(
                id: $type->id,
                code: $type->code,
                class: $type->class,
                profileTable: $type->profile_table,
                primaryDomain: $type->primary_domain,
                isMvp: (bool) $type->is_mvp,
                isActive: (bool) $type->is_active,
            ),
            workflowState: WorkflowState::from($model->workflow_state),
            moduleFlags: $model->module_flags ?? [],
            isIndexable: (bool) $model->is_indexable,
            publishedAt: $model->published_at?->toIso8601String(),
            publishedVersionId: $model->published_version_id,
            createdByUserId: $model->created_by_user_id,
            updatedByUserId: $model->updated_by_user_id,
            createdAt: $model->created_at?->toIso8601String(),
            updatedAt: $model->updated_at?->toIso8601String(),
        );
    }
}
