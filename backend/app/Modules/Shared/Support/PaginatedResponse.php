<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

final class PaginatedResponse
{
    /**
     * @param  Collection<int, mixed>|array<int, mixed>  $items
     */
    public static function fromPaginator(LengthAwarePaginator $paginator): JsonResponse
    {
        return ApiResponse::success(
            $paginator->items(),
            [
                'pagination' => [
                    'page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
        )->withHeaders([
            'Link' => self::buildLinkHeader($paginator),
        ]);
    }

    private static function buildLinkHeader(LengthAwarePaginator $paginator): string
    {
        $links = [];

        if ($paginator->previousPageUrl() !== null) {
            $links[] = '<'.$paginator->previousPageUrl().'>; rel="prev"';
        }

        if ($paginator->nextPageUrl() !== null) {
            $links[] = '<'.$paginator->nextPageUrl().'>; rel="next"';
        }

        return implode(', ', $links);
    }
}
