<?php

declare(strict_types=1);

namespace App\Modules\Search\Http\Controllers;

use App\Modules\Search\Application\Services\SearchService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SearchController
{
    public function __construct(
        private readonly SearchService $search,
    ) {}

    public function __invoke(Request $request, string $locale): JsonResponse
    {
        if (! config('wikiworld.search.enabled', false)) {
            return ApiResponse::error('SEARCH_DISABLED', 'Search is disabled.', 503);
        }

        $query = $request->string('q')->toString();

        if (mb_strlen($query) < 2) {
            return ApiResponse::error('VALIDATION_FAILED', 'Query must be at least 2 characters.', 400);
        }

        $filters = [
            'type' => $request->input('filter.type', $request->input('filter[type]')),
            'module' => $request->input('filter.module', $request->input('filter[module]')),
        ];

        $result = $this->search->search(
            $query,
            $locale,
            array_filter($filters, static fn ($v) => $v !== null && $v !== ''),
            max(1, (int) $request->input('page', 1)),
            min(50, max(1, (int) $request->input('per_page', 20))),
        );

        return ApiResponse::success($result['data'], meta: $result['meta']);
    }
}
