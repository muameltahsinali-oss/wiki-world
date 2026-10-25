<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\Services;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class ReadingHistoryService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function list(User $user, int $limit = 50): array
    {
        return DB::table('reading_history as rh')
            ->join('entities as e', 'e.id', '=', 'rh.entity_id')
            ->where('rh.user_id', $user->id)
            ->orderByDesc('rh.viewed_at')
            ->limit(min($limit, 200))
            ->get(['e.uuid as entity_uuid', 'rh.viewed_at'])
            ->map(static fn (object $row): array => [
                'entity_uuid' => $row->entity_uuid,
                'viewed_at' => Carbon::parse($row->viewed_at)->toIso8601String(),
            ])
            ->all();
    }

    public function clear(User $user): void
    {
        DB::table('reading_history')->where('user_id', $user->id)->delete();
    }

    public function record(User $user, string $entityUuid): array
    {
        $entity = Entity::query()->where('uuid', $entityUuid)->first();

        if ($entity === null) {
            throw (new ModelNotFoundException)->setModel(Entity::class, [$entityUuid]);
        }

        DB::table('reading_history')->updateOrInsert(
            [
                'user_id' => $user->id,
                'entity_id' => $entity->id,
            ],
            [
                'viewed_at' => now(),
            ],
        );

        return [
            'entity_uuid' => $entity->uuid,
            'viewed_at' => now()->toIso8601String(),
        ];
    }
}
