<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers;

use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Application\Services\ReadingHistoryService;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReadingHistoryController
{
    public function __construct(
        private readonly ReadingHistoryService $history,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return ApiResponse::success($this->history->list($user));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'entity_uuid' => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->history->record($user, $request->string('entity_uuid')->toString()),
            status: 201,
        );
    }

    public function destroy(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->history->clear($user);

        return ApiResponse::noContent();
    }
}
