<?php

declare(strict_types=1);

namespace App\Modules\Publishing\Application\Listeners;

use App\Modules\Publishing\Domain\Events\EntityPublished;
use App\Modules\Shared\Application\Services\FrontendRevalidationService;

final class RevalidateFrontendOnPublish
{
    public function __construct(
        private readonly FrontendRevalidationService $revalidation,
    ) {}

    public function handle(EntityPublished $event): void
    {
        $paths = $this->revalidation->pathsForEntity($event->entityId, $event->locale);
        $this->revalidation->revalidate($paths);
    }
}
