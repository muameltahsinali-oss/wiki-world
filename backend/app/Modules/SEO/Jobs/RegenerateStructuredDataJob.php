<?php

declare(strict_types=1);

namespace App\Modules\SEO\Jobs;

use App\Modules\SEO\Application\Services\SitemapService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class RegenerateStructuredDataJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $entityId,
        public readonly ?string $locale = null,
    ) {}

    public function handle(SitemapService $sitemap): void
    {
        $sitemap->upsertSeoMetadata($this->entityId, $this->locale);
    }
}
