<?php

declare(strict_types=1);

namespace App\Modules\Media\Http\Controllers;

use App\Modules\Media\Application\Services\MediaService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ListMediaController
{
    public function __construct(
        private readonly MediaService $media,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $filters = array_filter([
            'type' => $request->input('filter.type'),
            'status' => $request->input('filter.status'),
        ], static fn (mixed $v): bool => is_string($v) && $v !== '');

        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(1, (int) $request->input('per_page', 20)));

        $result = $this->media->list($filters, $page, $perPage);

        return ApiResponse::success($result['data'], meta: ['pagination' => $result['pagination']]);
    }
}
