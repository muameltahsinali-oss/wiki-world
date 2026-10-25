<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Actions;

use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\DTOs\CreateEntityDraftData;
use App\Modules\KnowledgeCore\Application\DTOs\CreateKnowledgeEntityData;
use App\Modules\KnowledgeCore\Application\DTOs\EntityDraftResult;
use App\Modules\KnowledgeCore\Application\Services\EntityLocalizationService;
use App\Modules\KnowledgeCore\Application\Services\EntityTypeRegistry;
use App\Modules\KnowledgeCore\Domain\Exceptions\DomainScopeDeniedException;
use App\Modules\KnowledgeCore\Domain\Exceptions\NonCreatableEntityTypeException;
use App\Modules\RolesPermissions\Application\Contracts\AuthorizationServiceInterface;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateEntityDraftAction
{
    /**
     * @var list<string>
     */
    private const NON_CREATABLE_TYPES = [
        'verse',
        'surah',
        'quran',
        'role',
        'permission',
        'user',
        'language',
    ];

    public function __construct(
        private readonly KnowledgeEntityRepositoryInterface $entities,
        private readonly EntityTypeRegistry $entityTypes,
        private readonly AuthorizationServiceInterface $authorization,
        private readonly EntityLocalizationService $localizations,
    ) {}

    public function execute(User $user, CreateEntityDraftData $data): EntityDraftResult
    {
        if (! $this->authorization->userCanCreateInDomain($user, $data->domain)) {
            throw new DomainScopeDeniedException;
        }

        if (in_array($data->entityTypeCode, self::NON_CREATABLE_TYPES, true)) {
            throw new NonCreatableEntityTypeException;
        }

        $this->entityTypes->getByCode($data->entityTypeCode);

        return DB::transaction(function () use ($user, $data): EntityDraftResult {
            $entity = $this->entities->create(new CreateKnowledgeEntityData(
                entityTypeCode: $data->entityTypeCode,
                createdByUserId: $user->id,
                moduleFlags: ['domain' => $data->domain],
            ));

            $localization = $this->localizations->upsertLocalization(
                entityId: $entity->id(),
                locale: $data->locale,
                title: $data->title,
                slug: $data->slug,
                summary: $data->summary,
            );

            return new EntityDraftResult(
                entity: $entity,
                domain: $data->domain,
                locale: $data->locale,
                title: $data->title,
                slug: $localization->slug,
                summary: $data->summary,
            );
        });
    }
}
