<?php

declare(strict_types=1);

namespace App\Modules\Publishing\Application\Actions;

use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Domain\Exceptions\DomainScopeDeniedException;
use App\Modules\Publishing\Application\Services\EntityWorkflowPersistenceService;
use App\Modules\Publishing\Application\Services\PublishingWorkflowService;
use App\Modules\RolesPermissions\Application\Contracts\AuthorizationServiceInterface;
use App\Modules\RolesPermissions\Domain\Enums\Permission;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class ReviewSubmissionAction
{
    public function __construct(
        private readonly KnowledgeEntityRepositoryInterface $entities,
        private readonly AuthorizationServiceInterface $authorization,
        private readonly PublishingWorkflowService $workflow,
        private readonly EntityWorkflowPersistenceService $persistence,
    ) {}

    /**
     * @return array{entity_uuid: string, workflow_state: string, decision: string}
     */
    public function execute(User $user, int $submissionId, string $decision, ?string $notes = null): array
    {
        if (! $this->authorization->userHasPermission($user, Permission::ReviewApprove->value)) {
            throw new DomainScopeDeniedException;
        }

        $submission = DB::table('editorial_submissions')->where('id', $submissionId)->first();

        if ($submission === null) {
            throw (new ModelNotFoundException)->setModel('editorial_submissions', [$submissionId]);
        }

        $entity = $this->entities->findByUuid(
            (string) DB::table('entities')->where('id', $submission->entity_id)->value('uuid'),
        );

        if ($entity === null) {
            throw (new ModelNotFoundException)->setModel('Entity', [$submission->entity_id]);
        }

        $action = match ($decision) {
            'approve' => 'approve',
            'reject' => 'reject',
            default => throw new \InvalidArgumentException('Decision must be approve or reject.'),
        };

        return DB::transaction(function () use ($user, $entity, $submissionId, $action, $notes, $decision): array {
            $newState = $this->workflow->resolve($entity->workflowState, $action);
            $entity = $this->persistence->persistWorkflowState($entity, $newState, $user);

            DB::table('editorial_reviews')->insert([
                'submission_id' => $submissionId,
                'reviewer_user_id' => $user->id,
                'decision' => $decision,
                'decision_notes' => $notes,
                'reviewed_at' => now(),
            ]);

            DB::table('editorial_submissions')
                ->where('id', $submissionId)
                ->update(['status' => $newState->value]);

            return [
                'entity_uuid' => $entity->uuid,
                'workflow_state' => $entity->workflowState->value,
                'decision' => $decision,
            ];
        });
    }
}
