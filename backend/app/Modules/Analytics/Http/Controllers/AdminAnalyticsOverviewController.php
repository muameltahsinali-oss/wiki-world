<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Http\Controllers;

use App\Modules\Analytics\Application\Services\PopularEntitiesService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminAnalyticsOverviewController
{
    public function __construct(
        private readonly PopularEntitiesService $popular,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $days = min(365, max(1, (int) $request->input('days', 30)));

        return ApiResponse::success($this->popular->adminOverview($days));
    }
}
