<?php

declare(strict_types=1);

namespace App\Modules\Ahlulbayt\Http\Controllers;

use App\Modules\Ahlulbayt\Application\Services\AhlulbaytService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AhlulbaytTimelineController
{
    public function __construct(
        private readonly AhlulbaytService $ahlulbayt,
    ) {}

    public function __invoke(Request $request, string $locale): JsonResponse
    {
        $limit = min(100, max(1, (int) $request->input('limit', 20)));

        return ApiResponse::success($this->ahlulbayt->getTimeline($locale, $limit));
    }
}
