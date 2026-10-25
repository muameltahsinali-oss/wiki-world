<?php

declare(strict_types=1);

namespace App\Modules\AI\Http\Controllers;

use App\Modules\AI\Application\Services\AiChatService;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class FlagMessageController
{
    public function __construct(
        private readonly AiChatService $chat,
    ) {}

    public function __invoke(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        /** @var User|null $user */
        $user = $request->user('sanctum');

        return ApiResponse::success(
            $this->chat->flagMessage($uuid, $user, $validated['comment'] ?? null),
            status: 201,
        );
    }
}
