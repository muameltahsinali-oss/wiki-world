<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\KnowledgeCore\Application\Services\EntityQueryService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class ListBookChaptersController
{
    public function __construct(
        private readonly EntityQueryService $queries,
    ) {}

    public function __invoke(string $locale, string $uuid): JsonResponse
    {
        return ApiResponse::success($this->queries->listBookChapters($uuid, $locale));
    }
}
