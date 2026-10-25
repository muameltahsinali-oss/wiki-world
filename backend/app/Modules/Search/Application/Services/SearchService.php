<?php

declare(strict_types=1);

namespace App\Modules\Search\Application\Services;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityLocalization;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityRelationship;
use App\Modules\Search\Application\Contracts\SearchIndexGatewayInterface;
use App\Modules\Search\Infrastructure\Gateways\DatabaseSearchGateway;
use App\Modules\Users\Infrastructure\Models\Language;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SearchService
{
    public function __construct(
        private readonly SearchIndexGatewayInterface $gateway,
        private readonly DatabaseSearchGateway $databaseFallback,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{data: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function search(string $query, string $locale, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $filters = array_merge($filters, ['locale' => $locale]);
        $page = max(1, $page);
        $perPage = min(50, max(1, $perPage));

        try {
            $raw = $this->gateway->search($query, $filters, $page, $perPage);
        } catch (Throwable $e) {
            Log::warning('Search gateway failed, using database fallback: '.$e->getMessage());
            $raw = $this->databaseFallback->search($query, $filters, $page, $perPage);
        }

        $data = $this->enrichHits($raw['hits'], $locale);

        return [
            'data' => $data,
            'meta' => [
                'query' => $query,
                'total' => $raw['total'],
                'took_ms' => $raw['took_ms'],
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'last_page' => max(1, (int) ceil($raw['total'] / $perPage)),
                ],
                'facets' => $raw['facets'],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function suggestions(string $query, string $locale, int $limit = 10): array
    {
        $limit = min(10, max(1, $limit));

        try {
            $suggestions = $this->gateway->suggest($query, $limit);
        } catch (Throwable $e) {
            Log::warning('Suggest gateway failed, using database fallback: '.$e->getMessage());
            $suggestions = $this->databaseFallback->suggest($query, $limit);
        }

        $entityIds = array_values(array_filter(array_map(
            static fn (array $row) => isset($row['entity_id']) ? (int) $row['entity_id'] : null,
            $suggestions,
        )));

        $entities = Entity::query()
            ->whereIn('id', $entityIds)
            ->get(['id', 'uuid'])
            ->keyBy('id');

        $slugs = $entityIds === []
            ? collect()
            : DB::table('entity_localizations')
                ->whereIn('entity_id', $entityIds)
                ->pluck('slug', 'entity_id');

        return array_map(static function (array $row) use ($entities, $slugs): array {
            $entity = isset($row['entity_id']) ? $entities->get((int) $row['entity_id']) : null;
            $entityId = isset($row['entity_id']) ? (int) $row['entity_id'] : null;

            return [
                'text' => (string) ($row['text'] ?? ''),
                'type' => (string) ($row['type'] ?? ''),
                'uuid' => $entity?->uuid,
                'slug' => $entityId !== null ? $slugs->get($entityId) : null,
            ];
        }, $suggestions);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function related(string $entityUuid, string $locale, int $limit = 10): array
    {
        $entity = Entity::query()->where('uuid', $entityUuid)->first();

        if ($entity === null) {
            return [];
        }

        $relatedIds = EntityRelationship::query()
            ->where(function ($q) use ($entity): void {
                $q->where('source_entity_id', $entity->id)
                    ->orWhere('target_entity_id', $entity->id);
            })
            ->get()
            ->map(static function (EntityRelationship $rel) use ($entity): int {
                return $rel->source_entity_id === $entity->id
                    ? (int) $rel->target_entity_id
                    : (int) $rel->source_entity_id;
            })
            ->unique()
            ->take($limit)
            ->values()
            ->all();

        if ($relatedIds === []) {
            $languageId = Language::query()->where('code', $locale)->value('id');
            $seedTitle = EntityLocalization::query()
                ->where('entity_id', $entity->id)
                ->when($languageId, fn ($q) => $q->where('language_id', $languageId))
                ->value('title');

            if (is_string($seedTitle) && mb_strlen($seedTitle) >= 2) {
                $result = $this->search(mb_substr($seedTitle, 0, 20), $locale, [], 1, $limit + 1);

                return array_values(array_filter(
                    $result['data'],
                    static fn (array $hit): bool => ($hit['uuid'] ?? null) !== $entityUuid,
                ));
            }

            return [];
        }

        $languageId = Language::query()->where('code', $locale)->value('id');

        return Entity::query()
            ->with('entityType')
            ->whereIn('id', $relatedIds)
            ->where('workflow_state', 'published')
            ->get()
            ->map(function (Entity $related) use ($languageId): array {
                $localization = EntityLocalization::query()
                    ->where('entity_id', $related->id)
                    ->when($languageId, fn ($q) => $q->where('language_id', $languageId))
                    ->first();

                return [
                    'uuid' => $related->uuid,
                    'type' => $related->entityType?->code,
                    'title' => $localization?->title,
                    'slug' => $localization?->slug,
                    'summary' => $localization?->summary,
                    'module' => $this->primaryModule(is_array($related->module_flags) ? $related->module_flags : []),
                ];
            })
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $hits
     * @return list<array<string, mixed>>
     */
    private function enrichHits(array $hits, string $locale): array
    {
        $entityIds = array_values(array_unique(array_filter(array_map(
            static fn (array $hit) => isset($hit['entity_id']) ? (int) $hit['entity_id'] : null,
            $hits,
        ))));

        $entities = Entity::query()
            ->whereIn('id', $entityIds)
            ->get(['id', 'uuid', 'module_flags'])
            ->keyBy('id');

        $languageId = Language::query()->where('code', $locale)->value('id');

        $localizations = EntityLocalization::query()
            ->whereIn('entity_id', $entityIds)
            ->when($languageId, fn ($q) => $q->where('language_id', $languageId))
            ->get()
            ->keyBy('entity_id');

        return array_map(function (array $hit) use ($entities, $localizations): array {
            $entityId = isset($hit['entity_id']) ? (int) $hit['entity_id'] : 0;
            $entity = $entities->get($entityId);
            $localization = $localizations->get($entityId);
            $moduleFlags = $hit['module_flags'] ?? ($entity?->module_flags ?? []);

            return [
                'uuid' => $entity?->uuid ?? ($hit['uuid'] ?? null),
                'type' => (string) ($hit['entity_type_code'] ?? ''),
                'title' => (string) ($hit['title'] ?? $localization?->title ?? ''),
                'slug' => $localization?->slug,
                'summary' => $localization?->summary,
                'highlight' => [
                    'title' => (string) ($hit['title'] ?? ''),
                    'summary' => $localization?->summary,
                ],
                'score' => isset($hit['popularity_score']) ? (float) $hit['popularity_score'] : null,
                'module' => $this->primaryModule(is_array($moduleFlags) ? $moduleFlags : []),
            ];
        }, $hits);
    }

    /**
     * @param  array<string, mixed>  $moduleFlags
     */
    private function primaryModule(array $moduleFlags): string
    {
        foreach (['quran', 'ahlulbayt', 'general', 'books_sources'] as $module) {
            if (($moduleFlags[$module] ?? false) === true) {
                return $module;
            }
        }

        return 'general';
    }
}
