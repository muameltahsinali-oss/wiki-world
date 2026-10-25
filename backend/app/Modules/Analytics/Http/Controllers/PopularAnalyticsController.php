<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Http\Controllers;

use App\Modules\Analytics\Application\Services\PopularEntitiesService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PopularAnalyticsController
{
    public function __construct(
        private readonly PopularEntitiesService $popular,
    ) {}

    public function __invoke(Request $request, string $locale): JsonResponse
    {
        $days = min(90, max(1, (int) $request->input('days', 7)));
        $limit = min(50, max(1, (int) $request->input('limit', 10)));

        return ApiResponse::success($this->popular->popular($locale, $days, $limit));
    }
}
