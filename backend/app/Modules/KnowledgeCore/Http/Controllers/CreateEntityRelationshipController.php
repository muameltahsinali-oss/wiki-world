<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\Services\EntityAccessService;
use App\Modules\KnowledgeCore\Application\Services\RelationshipService;
use App\Modules\KnowledgeCore\Domain\Exceptions\DomainScopeDeniedException;
use App\Modules\KnowledgeCore\Http\Requests\CreateEntityRelationshipRequest;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;

final class CreateEntityRelationshipController
{
    public function __construct(
        private readonly KnowledgeEntityRepositoryInterface $entities,
        private readonly EntityAccessService $access,
        private readonly RelationshipService $relationships,
    ) {}

    public function __invoke(CreateEntityRelationshipRequest $request, string $uuid): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $entity = $this->entities->findByUuid($uuid);

        if ($entity === null || ! $this->access->canUpdate($user, $entity)) {
            throw new DomainScopeDeniedException;
        }

        $relationship = $this->relationships->createRelationship(
            sourceEntityId: $entity->id(),
            targetEntityUuid: $request->string('target_entity_uuid')->toString(),
            relationshipTypeCode: $request->string('relationship_type')->toString(),
            metadata: $request->array('metadata'),
            userId: $user->id,
        );

        return ApiResponse::success([
            'id' => $relationship->id,
            'relationship_type' => $request->string('relationship_type')->toString(),
            'target_entity_uuid' => $request->string('target_entity_uuid')->toString(),
        ], [], 201);
    }
}
