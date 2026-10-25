<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Http\Controllers;

use App\Modules\Authentication\Application\Actions\RegisterUserAction;
use App\Modules\Authentication\Http\Requests\RegisterRequest;
use App\Modules\Authentication\Http\Resources\UserSummaryResource;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Application\DTOs\CreateUserData;
use Illuminate\Http\JsonResponse;

final class RegisterController
{
    public function __construct(
        private readonly RegisterUserAction $registerUser,
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = $this->registerUser->execute(new CreateUserData(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            preferredLocale: $request->string('preferred_locale')->toString() ?: null,
        ));

        return ApiResponse::success([
            'user' => UserSummaryResource::toArray($user),
            'message' => 'verification_sent',
        ], status: 201);
    }
}
