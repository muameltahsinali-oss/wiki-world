<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\KnowledgeCore\Application\Actions\RestoreEntityVersionAction;
use App\Modules\KnowledgeCore\Http\Resources\EditorialEntityResource;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RestoreEntityVersionController
{
    public function __construct(
        private readonly RestoreEntityVersionAction $restore,
    ) {}

    public function __invoke(Request $request, string $uuid, int $version): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $view = $this->restore->execute($user, $uuid, $version);

        return ApiResponse::success(EditorialEntityResource::fromView($view));
    }
}
