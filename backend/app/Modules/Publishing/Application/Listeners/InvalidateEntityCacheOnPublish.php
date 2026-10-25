<?php

declare(strict_types=1);

namespace App\Modules\Publishing\Application\Listeners;

use App\Modules\KnowledgeCore\Application\Services\EntityCacheService;
use App\Modules\Publishing\Application\Services\EntityWorkflowPersistenceService;
use App\Modules\Publishing\Domain\Events\EntityPublished;

final class InvalidateEntityCacheOnPublish
{
    public function __construct(
        private readonly EntityCacheService $cache,
        private readonly EntityWorkflowPersistenceService $persistence,
    ) {}

    public function handle(EntityPublished $event): void
    {
        foreach ($this->persistence->localesForEntity($event->entityId) as $locale) {
            $this->cache->forget($event->entityUuid, $locale);
        }
    }
}
