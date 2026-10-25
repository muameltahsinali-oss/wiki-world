<?php

declare(strict_types=1);

namespace App\Modules\Publishing\Application\Actions;

use App\Modules\Audit\Application\Contracts\AuditLoggerInterface;
use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\Services\EntityAccessService;
use App\Modules\KnowledgeCore\Application\Services\EntityCacheService;
use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\KnowledgeCore\Domain\Exceptions\DomainScopeDeniedException;
use App\Modules\Publishing\Application\Services\EntityWorkflowPersistenceService;
use App\Modules\Publishing\Application\Services\PublishingWorkflowService;
use App\Modules\Publishing\Domain\Events\EntityUnpublished;
use App\Modules\RolesPermissions\Application\Contracts\AuthorizationServiceInterface;
use App\Modules\RolesPermissions\Domain\Enums\Permission;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class UnpublishEntityAction
{
    public function __construct(
        private readonly KnowledgeEntityRepositoryInterface $entities,
        private readonly EntityAccessService $access,
        private readonly AuthorizationServiceInterface $authorization,
        private readonly PublishingWorkflowService $workflow,
        private readonly EntityWorkflowPersistenceService $persistence,
        private readonly EntityCacheService $cache,
        private readonly AuditLoggerInterface $audit,
    ) {}

    /**
     * @return array{entity_uuid: string, workflow_state: string}
     */
    public function execute(User $user, string $uuid): array
    {
        if (! $this->authorization->userHasPermission($user, Permission::EntityUnpublish->value)) {
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
            $newState = $this->workflow->resolve($entity->workflowState, 'unpublish');
            $entity = $this->persistence->persistWorkflowState($entity, $newState, $user);

            $versionId = $entity->publishedVersionId ?? $this->persistence->resolveLatestVersionId($entity->id());

            if ($versionId !== null) {
                DB::table('publish_events')->insert([
                    'entity_id' => $entity->id(),
                    'entity_version_id' => $versionId,
                    'action' => 'unpublish',
                    'performed_by_user_id' => $user->id,
                    'performed_at' => now(),
                ]);
            }

            $this->audit->log(
                action: 'workflow.transition',
                subjectType: 'entity',
                subjectId: $entity->id(),
                diff: [
                    'from' => WorkflowState::Published->value,
                    'to' => WorkflowState::Unpublished->value,
                    'action' => 'unpublish',
                ],
                actorUserId: $user->id,
            );

            foreach ($this->persistence->localesForEntity($entity->id()) as $locale) {
                $this->cache->forget($entity->uuid, $locale);
            }

            EntityUnpublished::dispatch(
                $entity->uuid,
                $entity->id(),
                $user->id,
            );

            return [
                'entity_uuid' => $entity->uuid,
                'workflow_state' => $entity->workflowState->value,
            ];
        });
    }
}
