<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\KnowledgeCore\Application\Services\EditorialInsightsService;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EditorialActivityController
{
    public function __construct(
        private readonly EditorialInsightsService $insights,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $action = $request->input('filter.action');

        return ApiResponse::success($this->insights->activity(
            $user,
            is_string($action) ? $action : null,
            (int) $request->input('limit', 50),
        ));
    }
}
