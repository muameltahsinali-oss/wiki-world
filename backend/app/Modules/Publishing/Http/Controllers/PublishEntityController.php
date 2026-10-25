<?php

declare(strict_types=1);

namespace App\Modules\Publishing\Http\Controllers;

use App\Modules\Publishing\Application\Actions\PublishEntityAction;
use App\Modules\Publishing\Http\Requests\PublishEntityRequest;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;

final class PublishEntityController
{
    public function __construct(
        private readonly PublishEntityAction $publishEntity,
    ) {}

    public function __invoke(PublishEntityRequest $request, string $uuid): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $result = $this->publishEntity->execute(
            user: $user,
            uuid: $uuid,
            locale: $request->string('locale')->toString(),
        );

        return ApiResponse::success($result);
    }
}
