<?php

declare(strict_types=1);

namespace App\Modules\Search\Application\Listeners;

use App\Modules\Publishing\Domain\Events\EntityUnpublished;
use App\Modules\Search\Jobs\IndexEntitySearchDocumentJob;

final class DeactivateEntityOnUnpublish
{
    public function handle(EntityUnpublished $event): void
    {
        if (! config('wikiworld.search.enabled', false)) {
            return;
        }

        IndexEntitySearchDocumentJob::dispatch($event->entityId, null, true);
    }
}
