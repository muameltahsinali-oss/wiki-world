<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\Services\EntityAccessService;
use App\Modules\KnowledgeCore\Application\Services\EntityVersionService;
use App\Modules\KnowledgeCore\Domain\Exceptions\DomainScopeDeniedException;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetEntityVersionController
{
    public function __construct(
        private readonly KnowledgeEntityRepositoryInterface $entities,
        private readonly EntityAccessService $access,
        private readonly EntityVersionService $versions,
    ) {}

    public function __invoke(Request $request, string $uuid, int $version): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $entity = $this->entities->findByUuid($uuid);

        if ($entity === null || ! $this->access->canViewDraft($user, $entity)) {
            throw new DomainScopeDeniedException;
        }

        return ApiResponse::success($this->versions->getVersionSnapshot($entity->id(), $version));
    }
}
