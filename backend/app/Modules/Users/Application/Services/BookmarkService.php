<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\Services;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\Users\Infrastructure\Models\Bookmark;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class BookmarkService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function list(User $user): array
    {
        return Bookmark::query()
            ->with('entity')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(static fn (Bookmark $bookmark): array => [
                'entity_uuid' => $bookmark->entity->uuid,
                'created_at' => $bookmark->created_at?->toIso8601String(),
            ])
            ->all();
    }

    /**
     * @return array{entity_uuid: string, created_at: string}
     */
    public function add(User $user, string $entityUuid): array
    {
        $entity = Entity::query()->where('uuid', $entityUuid)->first();

        if ($entity === null) {
            throw (new ModelNotFoundException)->setModel(Entity::class, [$entityUuid]);
        }

        $bookmark = Bookmark::query()->firstOrCreate([
            'user_id' => $user->id,
            'entity_id' => $entity->id,
        ], [
            'created_at' => now(),
        ]);

        return [
            'entity_uuid' => $entity->uuid,
            'created_at' => $bookmark->created_at?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }

    public function remove(User $user, string $entityUuid): void
    {
        $entityId = Entity::query()->where('uuid', $entityUuid)->value('id');

        if ($entityId === null) {
            throw (new ModelNotFoundException)->setModel(Entity::class, [$entityUuid]);
        }

        Bookmark::query()
            ->where('user_id', $user->id)
            ->where('entity_id', $entityId)
            ->delete();
    }
}
