<?php

declare(strict_types=1);

namespace App\Modules\Media\Http\Controllers;

use App\Modules\Media\Application\Services\MediaService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PatchMediaController
{
    public function __construct(
        private readonly MediaService $media,
    ) {}

    public function __invoke(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'alt_text' => ['required', 'string', 'max:512'],
        ]);

        return ApiResponse::success($this->media->updateMetadata(
            $uuid,
            $request->string('alt_text')->toString(),
        ));
    }
}
