<?php

declare(strict_types=1);

namespace App\Modules\Search\Infrastructure\Gateways;

use App\Modules\Search\Application\Contracts\SearchIndexGatewayInterface;
use App\Modules\Search\Infrastructure\Models\SearchDocument;
use App\Modules\Users\Infrastructure\Models\Language;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class DatabaseSearchGateway implements SearchIndexGatewayInterface
{
    public function index(array $document): void
    {
        // Persistence is handled by SearchIndexerService; gateway index is a no-op for database driver.
    }

    public function delete(string $meilisearchId): void
    {
        SearchDocument::query()
            ->where('meilisearch_id', $meilisearchId)
            ->update(['is_active' => false, 'updated_at' => now()]);
    }

    public function search(string $query, array $filters, int $page, int $perPage): array
    {
        $started = hrtime(true);
        $builder = $this->baseQuery($filters);

        if ($query !== '') {
            $this->applyTextSearch($builder, $query);
        }

        $total = (clone $builder)->count();
        $facets = $this->buildFacets(clone $builder);

        $hits = $builder
            ->orderByDesc('popularity_score')
            ->orderBy('title')
            ->forPage($page, $perPage)
            ->get()
            ->map(static fn (SearchDocument $doc): array => [
                'id' => $doc->id,
                'entity_id' => $doc->entity_id,
                'meilisearch_id' => $doc->meilisearch_id,
                'title' => $doc->title,
                'body_text' => $doc->body_text,
                'entity_type_code' => $doc->entity_type_code,
                'module_flags' => $doc->module_flags ?? [],
                'facets' => $doc->facets ?? [],
                'language_id' => $doc->language_id,
                'popularity_score' => (float) $doc->popularity_score,
            ])
            ->all();

        return [
            'hits' => $hits,
            'total' => $total,
            'facets' => $facets,
            'took_ms' => (int) ((hrtime(true) - $started) / 1_000_000),
        ];
    }

    public function suggest(string $query, int $limit): array
    {
        $builder = SearchDocument::query()->where('is_active', true);

        if ($query !== '') {
            $escaped = addcslashes($query, '%_\\');
            $builder->where('title', 'like', '%'.$escaped.'%');
        }

        return $builder
            ->orderByDesc('popularity_score')
            ->orderBy('title')
            ->limit($limit)
            ->get(['id', 'entity_id', 'title', 'entity_type_code', 'meilisearch_id', 'module_flags'])
            ->map(static fn (SearchDocument $doc): array => [
                'text' => $doc->title,
                'type' => $doc->entity_type_code,
                'entity_id' => $doc->entity_id,
                'meilisearch_id' => $doc->meilisearch_id,
                'module_flags' => $doc->module_flags ?? [],
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<SearchDocument>
     */
    private function baseQuery(array $filters): Builder
    {
        $builder = SearchDocument::query()->where('is_active', true);

        if (isset($filters['locale']) && is_string($filters['locale'])) {
            $languageId = Language::query()->where('code', $filters['locale'])->value('id');
            if ($languageId !== null) {
                $builder->where('language_id', $languageId);
            }
        }

        if (isset($filters['type']) && $filters['type'] !== '') {
            $types = array_values(array_filter(array_map('trim', explode(',', (string) $filters['type']))));
            if ($types !== []) {
                $builder->whereIn('entity_type_code', $types);
            }
        }

        if (isset($filters['module']) && is_string($filters['module']) && $filters['module'] !== '') {
            $module = $filters['module'];
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'pgsql') {
                $builder->whereRaw('(module_flags->>?)::boolean = true', [$module]);
            } else {
                $builder->where(function (Builder $q) use ($module): void {
                    $q->where('module_flags', 'like', '%"'.$module.'":true%')
                        ->orWhere('module_flags', 'like', '%"'.$module.'": true%');
                });
            }
        }

        return $builder;
    }

    /**
     * @param  Builder<SearchDocument>  $builder
     */
    private function applyTextSearch(Builder $builder, string $query): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $escaped = addcslashes($query, '%_\\');

        if ($driver === 'pgsql' && $this->hasTsvColumn()) {
            $builder->where(function (Builder $q) use ($query, $escaped): void {
                $q->whereRaw('tsv @@ plainto_tsquery(\'simple\', ?)', [$query])
                    ->orWhere('title', 'like', '%'.$escaped.'%')
                    ->orWhere('body_text', 'like', '%'.$escaped.'%');
            });

            return;
        }

        $builder->where(function (Builder $q) use ($escaped): void {
            $q->where('title', 'like', '%'.$escaped.'%')
                ->orWhere('body_text', 'like', '%'.$escaped.'%');
        });
    }

    private function hasTsvColumn(): bool
    {
        return Schema::hasColumn('search_documents', 'tsv');
    }

    /**
     * @param  Builder<SearchDocument>  $builder
     * @return array{type: list<array{value: string, count: int}>, module: list<array{value: string, count: int}>}
     */
    private function buildFacets(Builder $builder): array
    {
        $typeFacets = (clone $builder)
            ->select('entity_type_code', DB::raw('count(*) as aggregate'))
            ->groupBy('entity_type_code')
            ->get()
            ->map(static fn ($row): array => [
                'value' => (string) $row->entity_type_code,
                'count' => (int) $row->aggregate,
            ])
            ->values()
            ->all();

        $moduleCounts = [];
        (clone $builder)->get(['module_flags'])->each(function (SearchDocument $doc) use (&$moduleCounts): void {
            foreach (($doc->module_flags ?? []) as $key => $enabled) {
                if ($enabled === true) {
                    $moduleCounts[$key] = ($moduleCounts[$key] ?? 0) + 1;
                }
            }
        });

        $moduleFacets = [];
        foreach ($moduleCounts as $value => $count) {
            $moduleFacets[] = ['value' => (string) $value, 'count' => $count];
        }

        return [
            'type' => $typeFacets,
            'module' => $moduleFacets,
        ];
    }
}
