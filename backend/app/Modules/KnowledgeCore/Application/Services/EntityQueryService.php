<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Services;

use App\Modules\KnowledgeCore\Application\Mappers\EntityEditorialMapper;
use App\Modules\KnowledgeCore\Application\Mappers\EntityPublicMapper;
use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\KnowledgeCore\Infrastructure\Models\BookProfile;
use App\Modules\KnowledgeCore\Infrastructure\Models\ConceptProfile;
use App\Modules\KnowledgeCore\Infrastructure\Models\ContentBlock;
use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityLocalization;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityRelationship;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityVersion;
use App\Modules\KnowledgeCore\Infrastructure\Models\EventProfile;
use App\Modules\KnowledgeCore\Infrastructure\Models\PersonProfile;
use App\Modules\KnowledgeCore\Infrastructure\Models\PlaceProfile;
use App\Modules\KnowledgeCore\Infrastructure\Models\SourceProfile;
use App\Modules\Users\Infrastructure\Models\Language;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class EntityQueryService
{
    /**
     * @var array<string, class-string<Model>>
     */
    private const PROFILE_MODELS = [
        'person' => PersonProfile::class,
        'event' => EventProfile::class,
        'place' => PlaceProfile::class,
        'concept' => ConceptProfile::class,
        'source' => SourceProfile::class,
        'book' => BookProfile::class,
    ];

    public function __construct(
        private readonly EntityPublicMapper $publicMapper,
        private readonly EntityEditorialMapper $editorialMapper,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function loadEditorialView(string $uuid, string $locale): array
    {
        $view = $this->buildView($uuid, $locale, publishedOnly: false);

        return $this->editorialMapper->map($view);
    }

    /**
     * @return array<string, mixed>
     */
    public function loadPreviewView(string $uuid, string $locale): array
    {
        $view = $this->buildView($uuid, $locale, publishedOnly: false);

        return $this->publicMapper->map($view);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function loadPublicView(string $uuid, string $locale): ?array
    {
        $entity = Entity::query()->where('uuid', $uuid)->first();

        if ($entity === null || $entity->workflow_state !== WorkflowState::Published->value) {
            return null;
        }

        $view = $this->buildView($uuid, $locale, publishedOnly: true);

        return $this->publicMapper->map($view);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{data: list<array<string, mixed>>, pagination: array<string, int>}
     */
    public function listEditorial(array $filters, int $page, int $perPage): array
    {
        $languageId = Language::query()->where('code', $filters['locale'] ?? 'ar')->value('id');

        $query = Entity::query()
            ->with('entityType')
            ->join('entity_localizations', 'entities.id', '=', 'entity_localizations.entity_id')
            ->when($languageId !== null, static function (Builder $builder) use ($languageId): void {
                $builder->where('entity_localizations.language_id', $languageId);
            })
            ->select([
                'entities.*',
                'entity_localizations.title',
                'entity_localizations.slug',
                'entity_localizations.summary',
                'entity_localizations.workflow_state as localization_workflow_state',
            ]);

        if (isset($filters['type']) && is_string($filters['type'])) {
            $query->whereHas('entityType', static function (Builder $builder) use ($filters): void {
                $builder->where('code', $filters['type']);
            });
        }

        if (isset($filters['workflow_state']) && is_string($filters['workflow_state'])) {
            $query->where('entities.workflow_state', $filters['workflow_state']);
        }

        if (isset($filters['domain']) && is_string($filters['domain'])) {
            $query->where('entities.module_flags->domain', $filters['domain']);
        }

        if (isset($filters['q']) && is_string($filters['q']) && $filters['q'] !== '') {
            $query->where('entity_localizations.title', 'like', '%'.$filters['q'].'%');
        }

        $sort = $filters['sort'] ?? '-updated_at';
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        $sortColumn = match ($column) {
            'title' => 'entity_localizations.title',
            'workflow_state' => 'entities.workflow_state',
            default => 'entities.updated_at',
        };

        /** @var LengthAwarePaginator<int, Entity> $paginator */
        $paginator = $query
            ->orderBy($sortColumn, $direction)
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())
            ->map(function (Entity $entity) use ($filters): array {
                $view = [
                    'uuid' => $entity->uuid,
                    'entity_type' => $entity->entityType->code,
                    'domain' => (string) (($entity->module_flags ?? [])['domain'] ?? ''),
                    'workflow_state' => $entity->workflow_state,
                    'locale' => $filters['locale'] ?? 'ar',
                    'title' => $entity->title,
                    'slug' => $entity->slug,
                    'summary' => $entity->summary,
                    'relationships_count' => $this->countRelationships((int) $entity->id),
                    'updated_at' => $entity->updated_at?->toIso8601String(),
                ];

                return $this->editorialMapper->mapListItem($view);
            })
            ->all();

        return [
            'data' => $items,
            'pagination' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{data: list<array<string, mixed>>, pagination: array<string, int>}
     */
    public function listPublic(array $filters, int $page, int $perPage): array
    {
        $languageId = Language::query()->where('code', $filters['locale'] ?? 'ar')->value('id');

        $query = Entity::query()
            ->with(['entityType', 'personProfile'])
            ->join('entity_localizations', 'entities.id', '=', 'entity_localizations.entity_id')
            ->where('entities.workflow_state', WorkflowState::Published->value)
            ->when($languageId !== null, static function (Builder $builder) use ($languageId): void {
                $builder->where('entity_localizations.language_id', $languageId);
            })
            ->select([
                'entities.*',
                'entity_localizations.title',
                'entity_localizations.slug',
                'entity_localizations.summary',
            ]);

        if (isset($filters['type']) && is_string($filters['type'])) {
            $query->whereHas('entityType', static function (Builder $builder) use ($filters): void {
                $builder->where('code', $filters['type']);
            });
        }

        $module = $filters['module'] ?? $filters['domain'] ?? null;

        if (is_string($module) && $module !== '') {
            $query->where('entities.module_flags->domain', $module);
        }

        if (isset($filters['q']) && is_string($filters['q']) && $filters['q'] !== '') {
            $query->where('entity_localizations.title', 'like', '%'.$filters['q'].'%');
        }

        if (array_key_exists('is_masum', $filters) && $filters['is_masum'] !== null && $filters['is_masum'] !== '') {
            $isMasum = filter_var($filters['is_masum'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($isMasum !== null) {
                $query->whereHas('personProfile', static function (Builder $builder) use ($isMasum): void {
                    $builder->where('is_masum', $isMasum);
                });
            }
        }

        if (isset($filters['generation']) && $filters['generation'] !== null && $filters['generation'] !== '') {
            $generation = (int) $filters['generation'];
            $query->whereHas('personProfile', static function (Builder $builder) use ($generation): void {
                $builder->where('ahlulbayt_generation', $generation);
            });
        }

        $sort = $filters['sort'] ?? '-published_at';
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        $sortColumn = match ($column) {
            'title' => 'entity_localizations.title',
            default => 'entities.published_at',
        };

        /** @var LengthAwarePaginator<int, Entity> $paginator */
        $paginator = $query
            ->orderBy($sortColumn, $direction)
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())
            ->map(function (Entity $entity) use ($filters): array {
                $domain = (string) (($entity->module_flags ?? [])['domain'] ?? '');
                $view = [
                    'uuid' => $entity->uuid,
                    'entity_type' => $entity->entityType->code,
                    'domain' => $domain,
                    'locale' => $filters['locale'] ?? 'ar',
                    'title' => $entity->title,
                    'slug' => $entity->slug,
                    'summary' => $entity->summary,
                    'published_at' => $entity->published_at?->toIso8601String(),
                    'person_hints' => $this->buildPersonHints($entity),
                ];

                return $this->publicMapper->mapListItem($view);
            })
            ->all();

        return [
            'data' => $items,
            'pagination' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listBookChapters(string $bookUuid, string $locale): array
    {
        $bookId = Entity::query()
            ->where('uuid', $bookUuid)
            ->where('workflow_state', WorkflowState::Published->value)
            ->value('id');

        if ($bookId === null) {
            return [];
        }

        $languageId = Language::query()->where('code', $locale)->value('id');

        if ($languageId === null) {
            return [];
        }

        $partOfTypeId = DB::table('relationship_types')->where('code', 'part_of')->value('id');

        if ($partOfTypeId === null) {
            return [];
        }

        return DB::table('entity_relationships as er')
            ->join('entities as chapter_entities', 'chapter_entities.id', '=', 'er.source_entity_id')
            ->join('entity_localizations as el', 'chapter_entities.id', '=', 'el.entity_id')
            ->where('er.relationship_type_id', $partOfTypeId)
            ->where('er.target_entity_id', $bookId)
            ->where('chapter_entities.workflow_state', WorkflowState::Published->value)
            ->where('el.language_id', $languageId)
            ->orderBy('er.sort_order')
            ->orderBy('el.title')
            ->get(['chapter_entities.uuid', 'el.title', 'el.slug', 'er.sort_order'])
            ->map(static fn (object $row): array => [
                'uuid' => $row->uuid,
                'title' => $row->title,
                'slug' => $row->slug,
                'sort_order' => $row->sort_order,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildView(string $uuid, string $locale, bool $publishedOnly): array
    {
        $entity = Entity::query()
            ->with('entityType')
            ->where('uuid', $uuid)
            ->first();

        if ($entity === null) {
            throw (new ModelNotFoundException)->setModel(Entity::class, [$uuid]);
        }

        $language = Language::query()->where('code', $locale)->first();

        if ($language === null) {
            throw new \InvalidArgumentException('Unsupported locale.');
        }

        $localization = EntityLocalization::query()
            ->where('entity_id', $entity->id)
            ->where('language_id', $language->id)
            ->first();

        if ($localization === null) {
            throw (new ModelNotFoundException)->setModel(EntityLocalization::class, [$uuid, $locale]);
        }

        $versionId = $publishedOnly
            ? $entity->published_version_id
            : $this->resolveLatestVersionId((int) $entity->id);

        $versionNumber = null;

        if ($versionId !== null) {
            $versionNumber = EntityVersion::query()->where('id', $versionId)->value('version_number');
        }

        return [
            'uuid' => $entity->uuid,
            'entity_type' => $entity->entityType->code,
            'domain' => (string) (($entity->module_flags ?? [])['domain'] ?? ''),
            'workflow_state' => $entity->workflow_state,
            'locale' => $locale,
            'direction' => $language->direction,
            'title' => $localization->title,
            'slug' => $localization->slug,
            'summary' => $localization->summary,
            'translation_status' => $localization->translation_status,
            'meta_title' => $localization->meta_title,
            'meta_description' => $localization->meta_description,
            'is_indexable' => (bool) $entity->is_indexable,
            'published_at' => $entity->published_at?->toIso8601String(),
            'version_number' => $versionNumber,
            'profile' => $this->loadProfile($entity->entityType->code, (int) $entity->id),
            'blocks' => $this->loadBlocks((int) $entity->id, (int) $language->id, $versionId),
            'relationships_count' => $this->countRelationships((int) $entity->id, $publishedOnly),
            'created_at' => $entity->created_at?->toIso8601String(),
            'updated_at' => $entity->updated_at?->toIso8601String(),
        ];
    }

    private function resolveLatestVersionId(int $entityId): ?int
    {
        return EntityVersion::query()
            ->where('entity_id', $entityId)
            ->orderByDesc('version_number')
            ->value('id');
    }

    /**
     * @return array{is_masum: bool, imam_order: int|null, generation: int|null}|null
     */
    private function buildPersonHints(Entity $entity): ?array
    {
        if ($entity->entityType->code !== 'person' || $entity->personProfile === null) {
            return null;
        }

        return [
            'is_masum' => (bool) $entity->personProfile->is_masum,
            'imam_order' => $entity->personProfile->imam_order,
            'generation' => $entity->personProfile->ahlulbayt_generation,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadProfile(string $entityTypeCode, int $entityId): ?array
    {
        $modelClass = self::PROFILE_MODELS[$entityTypeCode] ?? null;

        if ($modelClass === null) {
            return null;
        }

        $profile = $modelClass::query()->where('entity_id', $entityId)->first();

        if ($profile === null) {
            return null;
        }

        return $profile->toArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function loadBlocks(int $entityId, int $languageId, ?int $versionId): array
    {
        if ($versionId === null) {
            return [];
        }

        return ContentBlock::query()
            ->where('entity_id', $entityId)
            ->where('language_id', $languageId)
            ->where('entity_version_id', $versionId)
            ->orderBy('sort_order')
            ->get()
            ->map(static fn (ContentBlock $block): array => [
                'type' => $block->block_type,
                'content' => $block->content,
                'heading_level' => $block->heading_level,
                'sort_order' => $block->sort_order,
            ])
            ->all();
    }

    private function countRelationships(int $entityId, bool $publishedOnly = false): int
    {
        $query = EntityRelationship::query()
            ->where(static function (Builder $builder) use ($entityId): void {
                $builder->where('source_entity_id', $entityId)
                    ->orWhere('target_entity_id', $entityId);
            });

        if ($publishedOnly) {
            $query->whereHas('sourceEntity', static function (Builder $builder): void {
                $builder->where('workflow_state', WorkflowState::Published->value);
            })->whereHas('targetEntity', static function (Builder $builder): void {
                $builder->where('workflow_state', WorkflowState::Published->value);
            });
        }

        return $query->count();
    }
}
