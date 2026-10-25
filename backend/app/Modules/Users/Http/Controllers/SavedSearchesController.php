<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers;

use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Application\Services\SavedSearchService;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SavedSearchesController
{
    public function __construct(
        private readonly SavedSearchService $searches,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return ApiResponse::success($this->searches->list($user));
    }

    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'query_payload' => ['required', 'array'],
        ]);

        return ApiResponse::success(
            $this->searches->create(
                $user,
                $request->string('name')->toString(),
                $request->input('query_payload'),
            ),
            status: 201,
        );
    }

    public function destroy(Request $request, string $uuid): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->searches->delete($user, $uuid);

        return ApiResponse::noContent();
    }
}
