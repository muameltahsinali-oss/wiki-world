<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\KnowledgeCore\Application\Services\EntityCacheService;
use App\Modules\KnowledgeCore\Application\Services\EntityQueryService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class GetPublicEntityController
{
    public function __construct(
        private readonly EntityQueryService $queries,
        private readonly EntityCacheService $cache,
    ) {}

    public function __invoke(string $locale, string $uuid): JsonResponse
    {
        $cached = $this->cache->get($uuid, $locale);

        if ($cached !== null) {
            return ApiResponse::success($cached);
        }

        $view = $this->queries->loadPublicView($uuid, $locale);

        if ($view === null) {
            throw new NotFoundHttpException;
        }

        $this->cache->set($uuid, $locale, $view);

        return ApiResponse::success($view);
    }
}
