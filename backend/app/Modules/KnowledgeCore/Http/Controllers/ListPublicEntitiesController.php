<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\KnowledgeCore\Application\Services\EntityQueryService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ListPublicEntitiesController
{
    public function __construct(
        private readonly EntityQueryService $queries,
    ) {}

    public function __invoke(Request $request, ?string $entityType = null): JsonResponse
    {
        $routeType = $request->route('entityType');

        $filters = array_filter([
            'locale' => $request->route('locale', config('wikiworld.default_locale', 'ar')),
            'type' => is_string($routeType) ? $routeType : ($entityType ?? $request->input('filter.type')),
            'module' => $request->input('filter.module'),
            'domain' => $request->input('filter.domain'),
            'q' => $request->input('filter.q') ?? $request->input('q'),
            'is_masum' => $request->input('filter.is_masum'),
            'generation' => $request->input('filter.generation'),
            'sort' => $request->input('sort'),
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(1, (int) $request->input('per_page', 20)));

        $result = $this->queries->listPublic($filters, $page, $perPage);

        return ApiResponse::success($result['data'], meta: ['pagination' => $result['pagination']]);
    }
}
