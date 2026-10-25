<?php

declare(strict_types=1);

namespace App\Modules\Media\Http\Controllers;

use App\Modules\Media\Application\Services\MediaService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AttachEntityMediaController
{
    public function __construct(
        private readonly MediaService $media,
    ) {}

    public function __invoke(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'media_uuid' => ['required', 'string'],
            'role' => ['nullable', 'string', 'max:32'],
        ]);

        $this->media->attachToEntity(
            $uuid,
            $request->string('media_uuid')->toString(),
            $request->string('role', 'inline')->toString(),
        );

        return ApiResponse::success(['attached' => true]);
    }
}
