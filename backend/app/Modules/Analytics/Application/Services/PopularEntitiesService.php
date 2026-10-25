<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Application\Services;

use Illuminate\Support\Facades\DB;

final class PopularEntitiesService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function popular(string $locale, int $days = 7, int $limit = 10): array
    {
        $since = now()->subDays(max(1, $days));
        $languageId = DB::table('languages')->where('code', $locale)->value('id');

        $rows = DB::table('analytics_events as ae')
            ->join('entities as e', 'e.id', '=', 'ae.entity_id')
            ->join('entity_types as et', 'et.id', '=', 'e.entity_type_id')
            ->leftJoin('entity_localizations as el', function ($join) use ($languageId): void {
                $join->on('el.entity_id', '=', 'e.id');
                if ($languageId !== null) {
                    $join->where('el.language_id', '=', $languageId);
                }
            })
            ->where('ae.event_type', 'page_view')
            ->where('ae.occurred_at', '>=', $since)
            ->where('e.workflow_state', 'published')
            ->groupBy('e.id', 'e.uuid', 'et.code', 'el.title', 'el.slug')
            ->selectRaw('e.uuid, et.code as entity_type, el.title, el.slug, COUNT(*) as view_count')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get();

        return $rows->map(static fn ($row): array => [
            'uuid' => $row->uuid,
            'entity_type' => $row->entity_type,
            'title' => $row->title,
            'slug' => $row->slug,
            'view_count' => (int) $row->view_count,
        ])->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function adminOverview(int $days = 30): array
    {
        $since = now()->subDays(max(1, $days));

        $totalViews = (int) DB::table('analytics_events')
            ->where('event_type', 'page_view')
            ->where('occurred_at', '>=', $since)
            ->count();

        $uniqueSessions = (int) DB::table('analytics_events')
            ->where('event_type', 'page_view')
            ->where('occurred_at', '>=', $since)
            ->whereNotNull('session_hash')
            ->selectRaw('COUNT(DISTINCT session_hash) as aggregate')
            ->value('aggregate');

        $topEntities = DB::table('analytics_events as ae')
            ->join('entities as e', 'e.id', '=', 'ae.entity_id')
            ->where('ae.event_type', 'page_view')
            ->where('ae.occurred_at', '>=', $since)
            ->groupBy('e.uuid')
            ->selectRaw('e.uuid, COUNT(*) as view_count')
            ->orderByDesc('view_count')
            ->limit(5)
            ->get()
            ->map(static fn ($row): array => [
                'uuid' => $row->uuid,
                'view_count' => (int) $row->view_count,
            ])
            ->all();

        return [
            'period_days' => $days,
            'total_page_views' => $totalViews,
            'unique_sessions' => $uniqueSessions,
            'top_entities' => $topEntities,
        ];
    }
}
