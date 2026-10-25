<?php

declare(strict_types=1);

namespace App\Modules\Ahlulbayt\Http\Controllers;

use App\Modules\Ahlulbayt\Application\Services\AhlulbaytService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AhlulbaytBrowseController
{
    public function __construct(
        private readonly AhlulbaytService $ahlulbayt,
    ) {}

    public function __invoke(Request $request, string $locale): JsonResponse
    {
        $filters = array_filter([
            'locale' => $locale,
            'type' => $request->input('filter.type'),
            'generation' => $request->input('filter.generation'),
            'is_masum' => $request->input('filter.is_masum'),
            'q' => $request->input('filter.q') ?? $request->input('q'),
            'sort' => $request->input('sort'),
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(1, (int) $request->input('per_page', 20)));

        $result = $this->ahlulbayt->browse($filters, $page, $perPage);

        return ApiResponse::success($result['data'], meta: ['pagination' => $result['pagination']]);
    }
}
