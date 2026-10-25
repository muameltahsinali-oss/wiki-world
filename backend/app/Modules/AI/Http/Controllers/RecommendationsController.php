<?php

declare(strict_types=1);

namespace App\Modules\AI\Http\Controllers;

use App\Modules\AI\Application\Services\AiChatService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RecommendationsController
{
    public function __construct(
        private readonly AiChatService $chat,
    ) {}

    public function __invoke(Request $request, string $locale): JsonResponse
    {
        return ApiResponse::success(
            $this->chat->recommendations(
                $locale,
                $request->input('entity_uuid'),
                (int) $request->input('limit', 5),
            ),
        );
    }
}
