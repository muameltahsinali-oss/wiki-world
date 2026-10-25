<?php

declare(strict_types=1);

namespace App\Modules\Search\Http\Controllers;

use App\Modules\Search\Application\Services\SearchService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SearchSuggestionsController
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

        if (mb_strlen($query) < 1) {
            return ApiResponse::error('VALIDATION_FAILED', 'Query is required.', 400);
        }

        return ApiResponse::success(
            $this->search->suggestions($query, $locale, (int) $request->input('limit', 10)),
        );
    }
}
