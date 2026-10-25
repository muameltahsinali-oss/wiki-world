<?php

declare(strict_types=1);

namespace App\Modules\Publishing\Http\Controllers;

use App\Modules\Publishing\Application\Actions\ArchiveEntityAction;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ArchiveEntityController
{
    public function __construct(
        private readonly ArchiveEntityAction $archiveEntity,
    ) {}

    public function __invoke(Request $request, string $uuid): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $result = $this->archiveEntity->execute($user, $uuid);

        return ApiResponse::success($result);
    }
}
