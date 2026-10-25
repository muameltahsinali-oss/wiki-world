<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers\Admin;

use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Application\Services\AdminUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AssignUserRoleController
{
    public function __construct(
        private readonly AdminUserService $users,
    ) {}

    public function __invoke(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'max:64'],
        ]);

        return ApiResponse::success($this->users->assignRole($uuid, $validated['role'], $request->user()?->id));
    }
}
