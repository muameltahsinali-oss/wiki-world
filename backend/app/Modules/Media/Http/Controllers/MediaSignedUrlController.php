<?php

declare(strict_types=1);

namespace App\Modules\Media\Http\Controllers;

use App\Modules\Media\Application\Services\MediaService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class MediaSignedUrlController
{
    public function __construct(
        private readonly MediaService $media,
    ) {}

    public function __invoke(string $uuid): JsonResponse
    {
        return ApiResponse::success($this->media->signedUrl($uuid));
    }
}
