<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Services;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class ReferenceService
{
    public function attachReference(
        int $entityId,
        string $sourceEntityUuid,
        ?string $pageReference = null,
        ?string $quoteExcerpt = null,
        ?int $userId = null,
    ): int {
        $sourceEntity = Entity::query()->where('uuid', $sourceEntityUuid)->first();

        if ($sourceEntity === null) {
            throw (new ModelNotFoundException)->setModel(Entity::class, [$sourceEntityUuid]);
        }

        return (int) DB::table('references')->insertGetId([
            'source_entity_id' => $sourceEntity->id,
            'referencer_type' => 'entity',
            'referencer_id' => $entityId,
            'entity_id' => $entityId,
            'page_reference' => $pageReference,
            'quote_excerpt' => $quoteExcerpt,
            'created_at' => now(),
        ]);
    }

    public function delete(int $referenceId, int $entityId): void
    {
        $deleted = DB::table('references')
            ->where('id', $referenceId)
            ->where('entity_id', $entityId)
            ->delete();

        if ($deleted === 0) {
            throw (new ModelNotFoundException)->setModel('Reference', [$referenceId]);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listForEntity(int $entityId): array
    {
        return DB::table('references')
            ->join('entities as sources', 'references.source_entity_id', '=', 'sources.id')
            ->where('references.entity_id', $entityId)
            ->orderBy('references.sort_order')
            ->orderBy('references.id')
            ->get([
                'references.id',
                'references.page_reference',
                'references.quote_excerpt',
                'references.created_at',
                'sources.uuid as source_uuid',
            ])
            ->map(static fn (object $row): array => [
                'id' => $row->id,
                'source_uuid' => $row->source_uuid,
                'page_reference' => $row->page_reference,
                'quote_excerpt' => $row->quote_excerpt,
                'created_at' => $row->created_at,
            ])
            ->all();
    }
}
