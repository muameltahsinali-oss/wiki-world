<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\Services\RelationshipService;
use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class GetPublicEntityRelationshipsController
{
    public function __construct(
        private readonly KnowledgeEntityRepositoryInterface $entities,
        private readonly RelationshipService $relationships,
    ) {}

    public function __invoke(string $locale, string $uuid): JsonResponse
    {
        $entity = Entity::query()->where('uuid', $uuid)->first();

        if ($entity === null || $entity->workflow_state !== WorkflowState::Published->value) {
            throw new NotFoundHttpException;
        }

        $relationships = $this->relationships->listForEntity((int) $entity->id, publishedOnly: true);

        return ApiResponse::success($relationships);
    }
}
