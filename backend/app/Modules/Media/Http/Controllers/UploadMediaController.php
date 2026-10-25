<?php

declare(strict_types=1);

namespace App\Modules\Media\Http\Controllers;

use App\Modules\Media\Application\Services\MediaService;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UploadMediaController
{
    public function __construct(
        private readonly MediaService $media,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'alt_text' => ['nullable', 'string', 'max:512'],
        ]);

        $result = $this->media->upload(
            $user,
            $request->file('file'),
            $request->input('alt_text'),
        );

        return ApiResponse::success($result, status: 201);
    }
}
