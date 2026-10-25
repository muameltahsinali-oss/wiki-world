<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\Services;

use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class SavedSearchService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function list(User $user): array
    {
        return DB::table('saved_searches')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(static fn (object $row): array => [
                'uuid' => $row->uuid,
                'name' => $row->name,
                'query_payload' => json_decode((string) $row->query_payload, true),
                'created_at' => Carbon::parse($row->created_at)->toIso8601String(),
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $queryPayload
     * @return array<string, mixed>
     */
    public function create(User $user, string $name, array $queryPayload): array
    {
        $uuid = (string) Str::uuid7();

        DB::table('saved_searches')->insert([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'name' => $name,
            'query_payload' => json_encode($queryPayload, JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);

        return [
            'uuid' => $uuid,
            'name' => $name,
            'query_payload' => $queryPayload,
            'created_at' => now()->toIso8601String(),
        ];
    }

    public function delete(User $user, string $uuid): void
    {
        DB::table('saved_searches')
            ->where('user_id', $user->id)
            ->where('uuid', $uuid)
            ->delete();
    }
}
