<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Application\Services;

use App\Modules\Analytics\Jobs\RecordAnalyticsEventJob;
use App\Modules\Shared\Application\Contracts\RateLimiterGatewayInterface;
use Illuminate\Support\Facades\DB;

final class AnalyticsIngestService
{
    private const RATE_LIMIT = 30;

    private const RATE_WINDOW = 60;

    public function __construct(
        private readonly RateLimiterGatewayInterface $rateLimiter,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function recordView(array $payload, ?int $userId = null): void
    {
        $sessionHash = hash('sha256', (string) ($payload['session_id'] ?? request()->ip().request()->userAgent()));

        $rateKey = 'analytics:views:'.$sessionHash;
        if ($this->rateLimiter->tooManyAttempts($rateKey, self::RATE_LIMIT)) {
            return;
        }
        $this->rateLimiter->hit($rateKey, self::RATE_WINDOW);

        $entityId = null;
        if (isset($payload['entity_uuid']) && is_string($payload['entity_uuid'])) {
            $entityId = (int) DB::table('entities')->where('uuid', $payload['entity_uuid'])->value('id');
            if ($entityId === 0) {
                $entityId = null;
            }
        }

        RecordAnalyticsEventJob::dispatch([
            'event_type' => 'page_view',
            'entity_id' => $entityId,
            'user_id' => $userId,
            'session_hash' => $sessionHash,
            'metadata' => array_filter([
                'locale' => $payload['locale'] ?? null,
                'path' => $payload['path'] ?? null,
                'duration_seconds' => $payload['duration_seconds'] ?? null,
                'referrer' => $payload['referrer'] ?? null,
            ], static fn (mixed $value): bool => $value !== null),
            'occurred_at' => now()->toIso8601String(),
        ]);
    }
}
