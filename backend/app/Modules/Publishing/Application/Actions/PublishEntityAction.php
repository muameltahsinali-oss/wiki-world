<?php

declare(strict_types=1);

namespace App\Modules\Publishing\Application\Actions;

use App\Modules\Audit\Application\Contracts\AuditLoggerInterface;
use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\Services\EntityAccessService;
use App\Modules\KnowledgeCore\Application\Services\EntityCacheService;
use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\KnowledgeCore\Domain\Exceptions\DomainScopeDeniedException;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityVersion;
use App\Modules\Publishing\Application\Services\EntityWorkflowPersistenceService;
use App\Modules\Publishing\Application\Services\PublishingWorkflowService;
use App\Modules\Publishing\Application\Services\PublishIntegrityValidator;
use App\Modules\Publishing\Domain\Events\EntityPublished;
use App\Modules\RolesPermissions\Application\Contracts\AuthorizationServiceInterface;
use App\Modules\RolesPermissions\Domain\Enums\Permission;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class PublishEntityAction
{
    public function __construct(
        private readonly KnowledgeEntityRepositoryInterface $entities,
        private readonly EntityAccessService $access,
        private readonly AuthorizationServiceInterface $authorization,
        private readonly PublishingWorkflowService $workflow,
        private readonly PublishIntegrityValidator $integrity,
        private readonly EntityWorkflowPersistenceService $persistence,
        private readonly EntityCacheService $cache,
        private readonly AuditLoggerInterface $audit,
    ) {}

    /**
     * @return array{entity_uuid: string, workflow_state: string, published_at: string}
     */
    public function execute(User $user, string $uuid, string $locale): array
    {
        if (! $this->authorization->userHasPermission($user, Permission::EntityPublish->value)) {
            throw new DomainScopeDeniedException;
        }

        $entity = $this->entities->findByUuid($uuid);

        if ($entity === null) {
            throw (new ModelNotFoundException)->setModel('Entity', [$uuid]);
        }

        if (! $this->access->canUpdate($user, $entity)) {
            throw new DomainScopeDeniedException;
        }

        $publishedState = $this->workflow->resolve($entity->workflowState, 'publish');
        $this->integrity->validate($entity->id(), $locale);

        return DB::transaction(function () use ($user, $entity, $locale, $publishedState): array {
            $versionId = $this->persistence->resolveLatestVersionId($entity->id());

            if ($versionId === null) {
                throw new \InvalidArgumentException('Entity must have at least one version before publishing.');
            }

            $publishedAt = now()->toIso8601String();

            $entity = $this->entities->save(
                $entity
                    ->withWorkflowState($publishedState)
                    ->withPublishedAt($publishedAt)
                    ->withPublishedVersionId($versionId)
                    ->withUpdatedByUserId($user->id),
            );

            $this->persistence->syncLocalizationWorkflowStates($entity->id(), $publishedState);

            EntityVersion::query()
                ->where('id', $versionId)
                ->update(['is_published_snapshot' => true]);

            DB::table('publish_events')->insert([
                'entity_id' => $entity->id(),
                'entity_version_id' => $versionId,
                'action' => 'publish',
                'performed_by_user_id' => $user->id,
                'performed_at' => now(),
                'metadata' => json_encode(['locale' => $locale]),
            ]);

            $this->audit->log(
                action: 'workflow.transition',
                subjectType: 'entity',
                subjectId: $entity->id(),
                diff: [
                    'from' => WorkflowState::Approved->value,
                    'to' => WorkflowState::Published->value,
                    'action' => 'publish',
                ],
                actorUserId: $user->id,
            );

            EntityPublished::dispatch(
                entityUuid: $entity->uuid,
                entityId: $entity->id(),
                versionId: $versionId,
                locale: $locale,
                performedByUserId: $user->id,
            );

            foreach ($this->persistence->localesForEntity($entity->id()) as $entityLocale) {
                $this->cache->forget($entity->uuid, $entityLocale);
            }

            return [
                'entity_uuid' => $entity->uuid,
                'workflow_state' => $entity->workflowState->value,
                'published_at' => $publishedAt,
            ];
        });
    }
}
