<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Resources;

use App\Modules\KnowledgeCore\Application\DTOs\EntityDraftResult;

final class EditorialEntityResource
{
    public static function fromDraft(EntityDraftResult $draft): array
    {
        return [
            'uuid' => $draft->entity->uuid,
            'entity_type' => $draft->entity->entityType->code,
            'domain' => $draft->domain,
            'workflow_state' => $draft->entity->workflowState->value,
            'locale' => $draft->locale,
            'title' => $draft->title,
            'slug' => $draft->slug,
            'summary' => $draft->summary,
            'is_indexable' => $draft->entity->isIndexable,
            'created_at' => $draft->entity->createdAt,
            'updated_at' => $draft->entity->updatedAt,
        ];
    }

    /**
     * @param  array<string, mixed>  $view
     * @return array<string, mixed>
     */
    public static function fromView(array $view): array
    {
        return $view;
    }
}
