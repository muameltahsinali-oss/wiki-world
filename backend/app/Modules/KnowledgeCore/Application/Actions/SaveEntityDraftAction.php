<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Actions;

use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\DTOs\SaveEntityDraftData;
use App\Modules\KnowledgeCore\Application\Services\ContentBlockValidator;
use App\Modules\KnowledgeCore\Application\Services\EntityAccessService;
use App\Modules\KnowledgeCore\Application\Services\EntityCacheService;
use App\Modules\KnowledgeCore\Application\Services\EntityLocalizationService;
use App\Modules\KnowledgeCore\Application\Services\EntityQueryService;
use App\Modules\KnowledgeCore\Application\Services\EntityVersionService;
use App\Modules\KnowledgeCore\Application\Services\ProfileService;
use App\Modules\KnowledgeCore\Domain\Exceptions\DomainScopeDeniedException;
use App\Modules\KnowledgeCore\Infrastructure\Models\ContentBlock;
use App\Modules\Users\Infrastructure\Models\Language;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class SaveEntityDraftAction
{
    public function __construct(
        private readonly KnowledgeEntityRepositoryInterface $entities,
        private readonly EntityAccessService $access,
        private readonly EntityLocalizationService $localizations,
        private readonly ProfileService $profiles,
        private readonly ContentBlockValidator $blockValidator,
        private readonly EntityVersionService $versions,
        private readonly EntityCacheService $cache,
        private readonly EntityQueryService $queries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(User $user, SaveEntityDraftData $data): array
    {
        $entity = $this->entities->findByUuid($data->uuid);

        if ($entity === null) {
            throw (new ModelNotFoundException)->setModel('Entity', [$data->uuid]);
        }

        if (! $this->access->canUpdate($user, $entity)) {
            throw new DomainScopeDeniedException;
        }

        return DB::transaction(function () use ($user, $data, $entity): array {
            $existingLocalization = $this->localizations->getByEntityAndLocale($entity->id(), $data->locale);

            if ($existingLocalization === null && $data->title === null) {
                throw new \InvalidArgumentException('Title is required when creating a new localization.');
            }

            $title = $data->title ?? $existingLocalization?->title ?? '';

            $localization = $this->localizations->upsertLocalization(
                entityId: $entity->id(),
                locale: $data->locale,
                title: $title,
                slug: $data->slug ?? $existingLocalization?->slug,
                summary: $data->summary ?? $existingLocalization?->summary,
            );

            if ($data->profile !== null) {
                $this->profiles->upsertProfile(
                    $entity->entityType->code,
                    $entity->id(),
                    $data->profile,
                );
            }

            $blocks = $data->blocks ?? [];

            if ($data->blocks !== null) {
                $this->blockValidator->validate($blocks);
            }

            $languageId = Language::query()->where('code', $data->locale)->value('id');

            $snapshot = [
                'locale' => $data->locale,
                'title' => $localization->title,
                'slug' => $localization->slug,
                'summary' => $localization->summary,
                'profile' => $data->profile,
                'blocks' => $blocks,
            ];

            $version = $this->versions->createVersion(
                entityId: $entity->id(),
                userId: $user->id,
                snapshot: $snapshot,
                changeSummary: 'Draft save',
            );

            if ($data->blocks !== null) {
                foreach ($blocks as $index => $block) {
                    $type = (string) ($block['type'] ?? $block['block_type'] ?? 'paragraph');
                    $content = $block['content'] ?? [];

                    if (is_string($content)) {
                        $content = ['text' => $content];
                    }

                    ContentBlock::query()->create([
                        'entity_id' => $entity->id(),
                        'entity_version_id' => $version->id,
                        'language_id' => (int) $languageId,
                        'sort_order' => (int) ($block['sort_order'] ?? $index),
                        'block_type' => $type,
                        'content' => $content,
                        'heading_level' => $block['heading_level'] ?? null,
                    ]);
                }
            }

            $this->entities->save(
                $entity->withUpdatedByUserId($user->id),
            );

            $this->cache->forget($data->uuid, $data->locale);

            return $this->queries->loadEditorialView($data->uuid, $data->locale);
        });
    }
}
