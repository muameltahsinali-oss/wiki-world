<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\Services\EntityAccessService;
use App\Modules\KnowledgeCore\Application\Services\RelationshipService;
use App\Modules\KnowledgeCore\Domain\Exceptions\DomainScopeDeniedException;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DeleteEntityRelationshipController
{
    public function __construct(
        private readonly KnowledgeEntityRepositoryInterface $entities,
        private readonly EntityAccessService $access,
        private readonly RelationshipService $relationships,
    ) {}

    public function __invoke(Request $request, string $uuid, int $relationshipId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $entity = $this->entities->findByUuid($uuid);

        if ($entity === null || ! $this->access->canUpdate($user, $entity)) {
            throw new DomainScopeDeniedException;
        }

        $this->relationships->delete($relationshipId);

        return ApiResponse::noContent();
    }
}
