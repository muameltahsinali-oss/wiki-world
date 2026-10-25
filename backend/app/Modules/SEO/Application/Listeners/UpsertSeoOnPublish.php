<?php

declare(strict_types=1);

namespace App\Modules\SEO\Application\Listeners;

use App\Modules\Publishing\Domain\Events\EntityPublished;
use App\Modules\SEO\Jobs\RegenerateStructuredDataJob;
use App\Modules\SEO\Jobs\UpsertSitemapEntryJob;

final class UpsertSeoOnPublish
{
    public function handle(EntityPublished $event): void
    {
        UpsertSitemapEntryJob::dispatch($event->entityId, $event->locale);
        RegenerateStructuredDataJob::dispatch($event->entityId, $event->locale);
    }
}
