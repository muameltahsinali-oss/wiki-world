<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Services;

use App\Modules\KnowledgeCore\Domain\Entities\KnowledgeEntity;
use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\Publishing\Domain\Exceptions\WorkflowConflictException;
use App\Modules\RolesPermissions\Application\Contracts\AuthorizationServiceInterface;
use App\Modules\RolesPermissions\Domain\Enums\Permission;
use App\Modules\Users\Infrastructure\Models\User;

final class EntityAccessService
{
    public function __construct(
        private readonly AuthorizationServiceInterface $authorization,
    ) {}

    public function getDomainFromEntity(KnowledgeEntity $entity): string
    {
        return (string) ($entity->moduleFlags['domain'] ?? '');
    }

    public function canViewDraft(User $user, KnowledgeEntity $entity): bool
    {
        return $this->authorization->userCanViewDraftInDomain(
            $user,
            $this->getDomainFromEntity($entity),
        );
    }

    public function canUpdate(User $user, KnowledgeEntity $entity): bool
    {
        if ($entity->workflowState === WorkflowState::InReview
            && ! $this->authorization->userHasPermission($user, Permission::ReviewApprove->value)) {
            throw new WorkflowConflictException;
        }

        return $this->authorization->userCanEditInDomain(
            $user,
            $this->getDomainFromEntity($entity),
        );
    }
}
