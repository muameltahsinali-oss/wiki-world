<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Services;

use App\Modules\KnowledgeCore\Infrastructure\Models\EntityVersion;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class EntityVersionService
{
    /**
     * @param  array<string, mixed>  $snapshot
     */
    public function createVersion(
        int $entityId,
        int $userId,
        array $snapshot,
        ?string $changeSummary = null,
    ): EntityVersion {
        return EntityVersion::query()->create([
            'entity_id' => $entityId,
            'version_number' => $this->getNextVersionNumber($entityId),
            'change_summary' => $changeSummary,
            'snapshot' => $snapshot,
            'is_published_snapshot' => false,
            'created_by_user_id' => $userId,
            'created_at' => now(),
        ]);
    }

    public function getNextVersionNumber(int $entityId): int
    {
        $currentMax = EntityVersion::query()
            ->where('entity_id', $entityId)
            ->max('version_number');

        return ((int) $currentMax) + 1;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listVersions(int $entityId): array
    {
        return EntityVersion::query()
            ->where('entity_id', $entityId)
            ->orderByDesc('version_number')
            ->get()
            ->map(static fn (EntityVersion $version): array => [
                'version_number' => $version->version_number,
                'change_summary' => $version->change_summary,
                'is_published_snapshot' => $version->is_published_snapshot,
                'created_by_user_id' => $version->created_by_user_id,
                'created_at' => $version->created_at?->toIso8601String(),
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function getVersionSnapshot(int $entityId, int $versionNumber): array
    {
        $version = EntityVersion::query()
            ->where('entity_id', $entityId)
            ->where('version_number', $versionNumber)
            ->first();

        if ($version === null) {
            throw (new ModelNotFoundException)->setModel(EntityVersion::class, [$entityId, $versionNumber]);
        }

        return [
            'version_number' => $version->version_number,
            'change_summary' => $version->change_summary,
            'is_published_snapshot' => $version->is_published_snapshot,
            'created_by_user_id' => $version->created_by_user_id,
            'created_at' => $version->created_at?->toIso8601String(),
            'snapshot' => $version->snapshot,
        ];
    }

    public function findVersion(int $entityId, int $versionNumber): EntityVersion
    {
        $version = EntityVersion::query()
            ->where('entity_id', $entityId)
            ->where('version_number', $versionNumber)
            ->first();

        if ($version === null) {
            throw (new ModelNotFoundException)->setModel(EntityVersion::class, [$entityId, $versionNumber]);
        }

        return $version;
    }
}
