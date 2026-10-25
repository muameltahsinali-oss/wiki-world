<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Services;

use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\KnowledgeCore\Domain\Exceptions\InvalidRelationshipException;
use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityRelationship;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class RelationshipService
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function createRelationship(
        int $sourceEntityId,
        string $targetEntityUuid,
        string $relationshipTypeCode,
        array $metadata,
        int $userId,
    ): EntityRelationship {
        $sourceEntity = Entity::query()->with('entityType')->find($sourceEntityId);

        if ($sourceEntity === null) {
            throw (new ModelNotFoundException)->setModel(Entity::class, [$sourceEntityId]);
        }

        $targetEntity = Entity::query()->with('entityType')->where('uuid', $targetEntityUuid)->first();

        if ($targetEntity === null) {
            throw (new ModelNotFoundException)->setModel(Entity::class, [$targetEntityUuid]);
        }

        $relationshipTypeId = DB::table('relationship_types')
            ->where('code', $relationshipTypeCode)
            ->where('is_active', true)
            ->value('id');

        if ($relationshipTypeId === null) {
            throw new InvalidRelationshipException('Unknown relationship type.');
        }

        $allowed = DB::table('relationship_type_allowed_pairs')
            ->where('relationship_type_id', $relationshipTypeId)
            ->where('source_entity_type_id', $sourceEntity->entity_type_id)
            ->where('target_entity_type_id', $targetEntity->entity_type_id)
            ->exists();

        if (! $allowed) {
            throw new InvalidRelationshipException('Relationship type is not allowed for this entity pair.');
        }

        return EntityRelationship::query()->create([
            'relationship_type_id' => $relationshipTypeId,
            'source_entity_id' => $sourceEntityId,
            'target_entity_id' => $targetEntity->id,
            'metadata' => $metadata,
            'created_by_user_id' => $userId,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listForEntity(int $entityId, bool $publishedOnly = false): array
    {
        $query = EntityRelationship::query()
            ->with([
                'sourceEntity.entityType',
                'targetEntity.entityType',
            ])
            ->where(function ($builder) use ($entityId): void {
                $builder->where('source_entity_id', $entityId)
                    ->orWhere('target_entity_id', $entityId);
            });

        if ($publishedOnly) {
            $query->whereHas('sourceEntity', static function ($builder): void {
                $builder->where('workflow_state', WorkflowState::Published->value);
            })->whereHas('targetEntity', static function ($builder): void {
                $builder->where('workflow_state', WorkflowState::Published->value);
            });
        }

        return $query
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(static function (EntityRelationship $relationship) use ($entityId): array {
                $typeCode = DB::table('relationship_types')
                    ->where('id', $relationship->relationship_type_id)
                    ->value('code');

                $isOutgoing = $relationship->source_entity_id === $entityId;
                $peer = $isOutgoing ? $relationship->targetEntity : $relationship->sourceEntity;

                return [
                    'id' => $relationship->id,
                    'relationship_type' => (string) $typeCode,
                    'direction' => $isOutgoing ? 'outgoing' : 'incoming',
                    'metadata' => $relationship->metadata,
                    'peer_entity' => [
                        'uuid' => $peer->uuid,
                        'entity_type' => $peer->entityType->code,
                        'workflow_state' => $peer->workflow_state,
                    ],
                    'created_at' => $relationship->created_at?->toIso8601String(),
                ];
            })
            ->all();
    }

    public function delete(int $relationshipId): void
    {
        $relationship = EntityRelationship::query()->find($relationshipId);

        if ($relationship === null) {
            throw (new ModelNotFoundException)->setModel(EntityRelationship::class, [$relationshipId]);
        }

        $relationship->delete();
    }
}
