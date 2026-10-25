<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\KnowledgeCore\Application\Services\EntityCacheService;
use App\Modules\KnowledgeCore\Application\Services\EntityQueryService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class GetPublicTypeEntityController
{
    /**
     * @var array<string, string>
     */
    private const ROUTE_TYPE_MAP = [
        'persons' => 'person',
        'articles' => 'article',
        'events' => 'event',
        'places' => 'place',
        'concepts' => 'concept',
        'sources' => 'source',
        'books' => 'book',
    ];

    public function __construct(
        private readonly EntityQueryService $queries,
        private readonly EntityCacheService $cache,
    ) {}

    public function __invoke(string $locale, string $typeRoute, string $uuid): JsonResponse
    {
        $expectedType = self::ROUTE_TYPE_MAP[$typeRoute] ?? null;

        if ($expectedType === null) {
            throw new NotFoundHttpException;
        }

        $cached = $this->cache->get($uuid, $locale);

        if ($cached !== null) {
            $cachedType = $cached['type'] ?? $cached['entity_type'] ?? null;

            if ($cachedType === $expectedType) {
                return ApiResponse::success($cached);
            }
        }

        $view = $this->queries->loadPublicView($uuid, $locale);

        if ($view === null || ($view['type'] ?? $view['entity_type'] ?? null) !== $expectedType) {
            throw new NotFoundHttpException;
        }

        $this->cache->set($uuid, $locale, $view);

        return ApiResponse::success($view);
    }
}
