<?php

declare(strict_types=1);

namespace App\Modules\Ahlulbayt\Application\Services;

use App\Modules\KnowledgeCore\Application\Services\EntityQueryService;
use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use Illuminate\Support\Facades\DB;

final class AhlulbaytService
{
    public function __construct(
        private readonly EntityQueryService $queries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getLanding(string $locale): array
    {
        $persons = $this->queries->listPublic([
            'locale' => $locale,
            'type' => 'person',
            'module' => 'ahlulbayt',
            'is_masum' => true,
            'sort' => 'title',
        ], 1, 6);

        $events = $this->queries->listPublic([
            'locale' => $locale,
            'type' => 'event',
            'module' => 'ahlulbayt',
            'sort' => '-published_at',
        ], 1, 4);

        $timeline = $this->getTimeline($locale, 5);

        return [
            'title' => 'أهل البيت (ع)',
            'stats' => $this->landingStats(),
            'featured_persons' => $persons['data'],
            'featured_events' => $events['data'],
            'timeline_preview' => $timeline,
        ];
    }

    /**
     * @return array<string, int>
     */
    public function landingStats(): array
    {
        $personCount = Entity::query()
            ->where('workflow_state', WorkflowState::Published->value)
            ->where('module_flags->domain', 'ahlulbayt')
            ->whereHas('entityType', static fn ($q) => $q->where('code', 'person'))
            ->whereHas('personProfile', static fn ($q) => $q->where('is_masum', true))
            ->count();

        $eventCount = Entity::query()
            ->where('workflow_state', WorkflowState::Published->value)
            ->where('module_flags->domain', 'ahlulbayt')
            ->whereHas('entityType', static fn ($q) => $q->where('code', 'event'))
            ->count();

        return [
            'masumin_count' => $personCount,
            'events_count' => $eventCount,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{data: list<array<string, mixed>>, pagination: array<string, int>}
     */
    public function browse(array $filters, int $page, int $perPage): array
    {
        $filters['module'] = 'ahlulbayt';

        return $this->queries->listPublic($filters, $page, $perPage);
    }

    /**
     * @return array<string, mixed>
     */
    public function getGenealogy(string $personUuid, string $locale, int $depth = 3): array
    {
        $root = $this->findPublishedPerson($personUuid, $locale);

        if ($root === null) {
            return ['root' => null, 'nodes' => [], 'edges' => []];
        }

        $parentOfTypeId = (int) DB::table('relationship_types')->where('code', 'parent_of')->value('id');
        $nodes = [$root];
        $edges = [];
        $visited = [$root['uuid'] => true];
        $frontier = [(int) $root['id']];

        for ($level = 0; $level < $depth && $frontier !== []; $level++) {
            $nextFrontier = [];

            foreach ($frontier as $parentId) {
                $children = DB::table('entity_relationships as er')
                    ->join('entities as child', 'child.id', '=', 'er.target_entity_id')
                    ->join('entity_localizations as el', 'el.entity_id', '=', 'child.id')
                    ->join('languages as lang', 'lang.id', '=', 'el.language_id')
                    ->leftJoin('person_profiles as pp', 'pp.entity_id', '=', 'child.id')
                    ->where('er.relationship_type_id', $parentOfTypeId)
                    ->where('er.source_entity_id', $parentId)
                    ->where('child.workflow_state', WorkflowState::Published->value)
                    ->where('lang.code', $locale)
                    ->get([
                        'child.id',
                        'child.uuid',
                        'el.title',
                        'pp.ahlulbayt_generation as generation',
                    ]);

                $parentUuid = Entity::query()->where('id', $parentId)->value('uuid');

                foreach ($children as $child) {
                    $edges[] = [
                        'from' => $parentUuid,
                        'to' => $child->uuid,
                        'type' => 'child_of',
                    ];

                    if (! isset($visited[$child->uuid])) {
                        $visited[$child->uuid] = true;
                        $nodes[] = [
                            'uuid' => $child->uuid,
                            'title' => $child->title,
                            'parent_uuid' => $parentUuid,
                            'generation' => $child->generation,
                        ];
                        $nextFrontier[] = (int) $child->id;
                    }
                }
            }

            $frontier = $nextFrontier;
        }

        return [
            'root' => [
                'uuid' => $root['uuid'],
                'title' => $root['title'],
            ],
            'nodes' => $nodes,
            'edges' => $edges,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getLineage(string $personUuid, string $locale): array
    {
        $person = $this->findPublishedPerson($personUuid, $locale);

        if ($person === null) {
            return ['ancestors' => [], 'descendants' => []];
        }

        $parentOfTypeId = (int) DB::table('relationship_types')->where('code', 'parent_of')->value('id');

        return [
            'ancestors' => $this->walkLineage((int) $person['id'], $parentOfTypeId, $locale, direction: 'ancestors'),
            'descendants' => $this->walkLineage((int) $person['id'], $parentOfTypeId, $locale, direction: 'descendants'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getTimeline(string $locale, int $limit = 20): array
    {
        $languageId = DB::table('languages')->where('code', $locale)->value('id');

        return Entity::query()
            ->with('entityType')
            ->join('entity_localizations', 'entities.id', '=', 'entity_localizations.entity_id')
            ->leftJoin('event_profiles', 'entities.id', '=', 'event_profiles.entity_id')
            ->where('entities.workflow_state', WorkflowState::Published->value)
            ->where('entities.module_flags->domain', 'ahlulbayt')
            ->whereHas('entityType', static fn ($q) => $q->where('code', 'event'))
            ->when($languageId !== null, static fn ($q) => $q->where('entity_localizations.language_id', $languageId))
            ->orderBy('event_profiles.start_date_gregorian')
            ->limit($limit)
            ->get([
                'entities.uuid',
                'entity_localizations.title',
                'entity_localizations.slug',
                'entity_localizations.summary',
                'event_profiles.start_date_gregorian',
                'event_profiles.era_code',
            ])
            ->map(static fn ($row): array => [
                'uuid' => $row->uuid,
                'title' => $row->title,
                'slug' => $row->slug,
                'summary' => $row->summary,
                'start_date' => $row->start_date_gregorian,
                'era_code' => $row->era_code,
            ])
            ->all();
    }

    /**
     * @return array{id: int, uuid: string, title: string}|null
     */
    private function findPublishedPerson(string $uuid, string $locale): ?array
    {
        $languageId = DB::table('languages')->where('code', $locale)->value('id');

        $row = Entity::query()
            ->join('entity_localizations', 'entities.id', '=', 'entity_localizations.entity_id')
            ->where('entities.uuid', $uuid)
            ->where('entities.workflow_state', WorkflowState::Published->value)
            ->where('entities.module_flags->domain', 'ahlulbayt')
            ->whereHas('entityType', static fn ($q) => $q->where('code', 'person'))
            ->when($languageId !== null, static fn ($q) => $q->where('entity_localizations.language_id', $languageId))
            ->first(['entities.id', 'entities.uuid', 'entity_localizations.title']);

        if ($row === null) {
            return null;
        }

        return [
            'id' => (int) $row->id,
            'uuid' => $row->uuid,
            'title' => $row->title,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function walkLineage(int $personId, int $parentOfTypeId, string $locale, string $direction): array
    {
        $languageId = DB::table('languages')->where('code', $locale)->value('id');
        $results = [];
        $visited = [];
        $queue = [$personId];

        while ($queue !== []) {
            $currentId = array_shift($queue);

            if ($direction === 'ancestors') {
                $related = DB::table('entity_relationships as er')
                    ->join('entities as related', 'related.id', '=', 'er.source_entity_id')
                    ->join('entity_localizations as el', 'el.entity_id', '=', 'related.id')
                    ->where('er.relationship_type_id', $parentOfTypeId)
                    ->where('er.target_entity_id', $currentId)
                    ->where('related.workflow_state', WorkflowState::Published->value)
                    ->when($languageId !== null, static fn ($q) => $q->where('el.language_id', $languageId))
                    ->get(['related.id', 'related.uuid', 'el.title']);
            } else {
                $related = DB::table('entity_relationships as er')
                    ->join('entities as related', 'related.id', '=', 'er.target_entity_id')
                    ->join('entity_localizations as el', 'el.entity_id', '=', 'related.id')
                    ->where('er.relationship_type_id', $parentOfTypeId)
                    ->where('er.source_entity_id', $currentId)
                    ->where('related.workflow_state', WorkflowState::Published->value)
                    ->when($languageId !== null, static fn ($q) => $q->where('el.language_id', $languageId))
                    ->get(['related.id', 'related.uuid', 'el.title']);
            }

            foreach ($related as $row) {
                if (isset($visited[$row->uuid])) {
                    continue;
                }

                $visited[$row->uuid] = true;

                if ((int) $row->id !== $personId) {
                    $results[] = [
                        'uuid' => $row->uuid,
                        'title' => $row->title,
                    ];
                }

                $queue[] = (int) $row->id;
            }
        }

        return $results;
    }
}
