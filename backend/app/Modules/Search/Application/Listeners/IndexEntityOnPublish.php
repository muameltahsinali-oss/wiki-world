<?php

declare(strict_types=1);

namespace App\Modules\Search\Application\Listeners;

use App\Modules\Publishing\Domain\Events\EntityPublished;
use App\Modules\Search\Jobs\IndexEntitySearchDocumentJob;

final class IndexEntityOnPublish
{
    public function handle(EntityPublished $event): void
    {
        if (! config('wikiworld.search.enabled', false)) {
            return;
        }

        IndexEntitySearchDocumentJob::dispatch($event->entityId, $event->locale);
    }
}
