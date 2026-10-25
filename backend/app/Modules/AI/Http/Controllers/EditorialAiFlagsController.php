<?php

declare(strict_types=1);

namespace App\Modules\AI\Http\Controllers;

use App\Modules\AI\Application\Services\AiChatService;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EditorialAiFlagsController
{
    public function __construct(
        private readonly AiChatService $chat,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->chat->listFlags((int) $request->input('limit', 50)),
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,resolved,dismissed'],
        ]);

        /** @var User $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->chat->resolveFlag($id, $user, $validated['status']),
        );
    }
}
