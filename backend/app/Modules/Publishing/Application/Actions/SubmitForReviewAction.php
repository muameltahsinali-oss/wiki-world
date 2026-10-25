<?php

declare(strict_types=1);

namespace App\Modules\Publishing\Application\Actions;

use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\Services\EntityAccessService;
use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\KnowledgeCore\Domain\Exceptions\DomainScopeDeniedException;
use App\Modules\Publishing\Application\Services\EntityWorkflowPersistenceService;
use App\Modules\Publishing\Application\Services\PublishingWorkflowService;
use App\Modules\RolesPermissions\Application\Contracts\AuthorizationServiceInterface;
use App\Modules\RolesPermissions\Domain\Enums\Permission;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class SubmitForReviewAction
{
    public function __construct(
        private readonly KnowledgeEntityRepositoryInterface $entities,
        private readonly EntityAccessService $access,
        private readonly AuthorizationServiceInterface $authorization,
        private readonly PublishingWorkflowService $workflow,
        private readonly EntityWorkflowPersistenceService $persistence,
    ) {}

    /**
     * @return array{entity_uuid: string, workflow_state: string, submission_id: int}
     */
    public function execute(User $user, string $uuid): array
    {
        if (! $this->authorization->userHasPermission($user, Permission::EntitySubmit->value)) {
            throw new DomainScopeDeniedException;
        }

        $entity = $this->entities->findByUuid($uuid);

        if ($entity === null) {
            throw (new ModelNotFoundException)->setModel('Entity', [$uuid]);
        }

        if (! $this->access->canUpdate($user, $entity)) {
            throw new DomainScopeDeniedException;
        }

        return DB::transaction(function () use ($user, $entity): array {
            $submittedState = $this->workflow->resolve($entity->workflowState, 'submit');
            $entity = $this->persistence->persistWorkflowState($entity, $submittedState, $user);

            $inReviewState = $this->workflow->resolve($entity->workflowState, 'auto_review');
            $entity = $this->persistence->persistWorkflowState($entity, $inReviewState, $user);

            $versionId = $this->persistence->resolveLatestVersionId($entity->id());

            if ($versionId === null) {
                throw new \InvalidArgumentException('Entity must have at least one version before submission.');
            }

            $submissionId = (int) DB::table('editorial_submissions')->insertGetId([
                'entity_id' => $entity->id(),
                'entity_version_id' => $versionId,
                'submitted_by_user_id' => $user->id,
                'status' => WorkflowState::InReview->value,
                'checklist_completed' => json_encode([]),
                'submitted_at' => now(),
            ]);

            return [
                'entity_uuid' => $entity->uuid,
                'workflow_state' => $entity->workflowState->value,
                'submission_id' => $submissionId,
            ];
        });
    }
}
