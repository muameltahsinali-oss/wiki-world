<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers\Admin;

use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Application\Services\AdminUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RemoveUserRoleController
{
    public function __construct(
        private readonly AdminUserService $users,
    ) {}

    public function __invoke(Request $request, string $uuid, string $role): JsonResponse
    {
        return ApiResponse::success($this->users->removeRole($uuid, $role, $request->user()?->id));
    }
}
