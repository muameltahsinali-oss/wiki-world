<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Actions;

use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\DTOs\SaveEntityDraftData;
use App\Modules\KnowledgeCore\Application\Services\EntityAccessService;
use App\Modules\KnowledgeCore\Application\Services\EntityVersionService;
use App\Modules\KnowledgeCore\Domain\Exceptions\DomainScopeDeniedException;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class RestoreEntityVersionAction
{
    public function __construct(
        private readonly KnowledgeEntityRepositoryInterface $entities,
        private readonly EntityAccessService $access,
        private readonly EntityVersionService $versions,
        private readonly SaveEntityDraftAction $saveDraft,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(User $user, string $uuid, int $versionNumber): array
    {
        $entity = $this->entities->findByUuid($uuid);

        if ($entity === null) {
            throw (new ModelNotFoundException)->setModel('Entity', [$uuid]);
        }

        if (! $this->access->canUpdate($user, $entity)) {
            throw new DomainScopeDeniedException;
        }

        $version = $this->versions->findVersion($entity->id(), $versionNumber);
        $snapshot = $version->snapshot;

        if (! is_array($snapshot)) {
            throw new \InvalidArgumentException('Version snapshot is invalid.');
        }

        return $this->saveDraft->execute($user, new SaveEntityDraftData(
            uuid: $uuid,
            locale: (string) ($snapshot['locale'] ?? 'ar'),
            title: $snapshot['title'] ?? null,
            slug: $snapshot['slug'] ?? null,
            summary: $snapshot['summary'] ?? null,
            profile: $snapshot['profile'] ?? null,
            blocks: $snapshot['blocks'] ?? null,
        ));
    }
}
