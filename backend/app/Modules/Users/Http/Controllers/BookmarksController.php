<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers;

use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Application\Services\BookmarkService;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BookmarksController
{
    public function __construct(
        private readonly BookmarkService $bookmarks,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return ApiResponse::success($this->bookmarks->list($user));
    }

    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $request->validate(['entity_uuid' => ['required', 'string']]);

        return ApiResponse::success(
            $this->bookmarks->add($user, $request->string('entity_uuid')->toString()),
            status: 201,
        );
    }

    public function destroy(Request $request, string $entityUuid): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->bookmarks->remove($user, $entityUuid);

        return ApiResponse::noContent();
    }
}
