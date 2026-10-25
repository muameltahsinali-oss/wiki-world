<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Http\Controllers;

use App\Modules\Authentication\Application\Actions\LoginUserAction;
use App\Modules\Authentication\Http\Requests\LoginRequest;
use App\Modules\Authentication\Http\Resources\UserSummaryResource;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class LoginController
{
    public function __construct(
        private readonly LoginUserAction $loginUser,
    ) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $result = $this->loginUser->execute(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            deviceName: $request->string('device_name')->toString() ?: null,
        );

        return ApiResponse::success([
            'token' => $result->token,
            'token_type' => $result->tokenType,
            'expires_at' => $result->expiresAt,
            'user' => UserSummaryResource::toArray($result->user),
        ]);
    }
}
