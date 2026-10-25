<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

final class RecordAnalyticsEventJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $event
     */
    public function __construct(
        public readonly array $event,
    ) {}

    public function handle(): void
    {
        DB::table('analytics_events')->insert([
            'event_type' => $this->event['event_type'],
            'entity_id' => $this->event['entity_id'] ?? null,
            'user_id' => $this->event['user_id'] ?? null,
            'session_hash' => $this->event['session_hash'] ?? null,
            'metadata' => isset($this->event['metadata'])
                ? json_encode($this->event['metadata'], JSON_THROW_ON_ERROR)
                : null,
            'occurred_at' => $this->event['occurred_at'] ?? now(),
        ]);

        if (($this->event['event_type'] ?? '') === 'page_view' && ! empty($this->event['entity_id'])) {
            $date = now()->toDateString();
            DB::table('entity_popularity_daily')->upsert(
                [
                    'entity_id' => $this->event['entity_id'],
                    'date' => $date,
                    'view_count' => 1,
                    'unique_sessions' => 1,
                    'bookmark_adds' => 0,
                ],
                ['entity_id', 'date'],
                [
                    'view_count' => DB::raw('view_count + 1'),
                ],
            );
        }
    }
}
