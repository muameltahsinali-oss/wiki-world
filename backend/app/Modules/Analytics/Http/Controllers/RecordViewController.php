<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Http\Controllers;

use App\Modules\Analytics\Application\Services\AnalyticsIngestService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RecordViewController
{
    public function __construct(
        private readonly AnalyticsIngestService $analytics,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entity_uuid' => ['nullable', 'uuid'],
            'locale' => ['nullable', 'string', 'in:ar,en'],
            'path' => ['nullable', 'string', 'max:2048'],
            'session_id' => ['nullable', 'string', 'max:128'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'referrer' => ['nullable', 'string', 'max:2048'],
        ]);

        $this->analytics->recordView($validated, $request->user()?->id);

        return ApiResponse::success(['accepted' => true], status: 202);
    }
}
