<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers\Admin;

use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Application\Services\AdminUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CreateAdminUserController
{
    public function __construct(
        private readonly AdminUserService $users,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'preferred_locale' => ['nullable', 'string', 'in:ar,en'],
            'role' => ['nullable', 'string', 'max:64'],
            'domain_scopes' => ['nullable', 'array'],
            'domain_scopes.*' => ['string', 'max:64'],
        ]);

        $user = $this->users->create($validated, $request->user()?->id);

        return ApiResponse::success($user, status: 201);
    }
}
