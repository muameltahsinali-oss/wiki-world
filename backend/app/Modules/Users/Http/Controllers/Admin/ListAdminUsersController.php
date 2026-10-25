<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers\Admin;

use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Application\Services\AdminUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ListAdminUsersController
{
    public function __construct(
        private readonly AdminUserService $users,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(1, (int) $request->input('per_page', 20)));
        $query = $request->input('q');

        $result = $this->users->list($page, $perPage, is_string($query) ? $query : null);

        return ApiResponse::success($result['data'], meta: ['pagination' => $result['pagination']]);
    }
}
