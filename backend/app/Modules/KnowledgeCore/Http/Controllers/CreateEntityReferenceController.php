<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\Services\EntityAccessService;
use App\Modules\KnowledgeCore\Application\Services\ReferenceService;
use App\Modules\KnowledgeCore\Domain\Exceptions\DomainScopeDeniedException;
use App\Modules\KnowledgeCore\Http\Requests\CreateEntityReferenceRequest;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;

final class CreateEntityReferenceController
{
    public function __construct(
        private readonly KnowledgeEntityRepositoryInterface $entities,
        private readonly EntityAccessService $access,
        private readonly ReferenceService $references,
    ) {}

    public function __invoke(CreateEntityReferenceRequest $request, string $uuid): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $entity = $this->entities->findByUuid($uuid);

        if ($entity === null || ! $this->access->canUpdate($user, $entity)) {
            throw new DomainScopeDeniedException;
        }

        $referenceId = $this->references->attachReference(
            entityId: $entity->id(),
            sourceEntityUuid: $request->string('source_entity_uuid')->toString(),
            pageReference: $request->has('page_reference') ? $request->string('page_reference')->toString() : null,
            quoteExcerpt: $request->has('quote_excerpt') ? $request->string('quote_excerpt')->toString() : null,
            userId: $user->id,
        );

        return ApiResponse::success(['id' => $referenceId], [], 201);
    }
}
