<?php

declare(strict_types=1);

namespace App\Modules\Ahlulbayt\Http\Controllers;

use App\Modules\Ahlulbayt\Application\Services\AhlulbaytService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class AhlulbaytLineageController
{
    public function __construct(
        private readonly AhlulbaytService $ahlulbayt,
    ) {}

    public function __invoke(string $locale, string $uuid): JsonResponse
    {
        return ApiResponse::success($this->ahlulbayt->getLineage($uuid, $locale));
    }
}
