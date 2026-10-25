<?php

declare(strict_types=1);

namespace App\Modules\Search\Jobs;

use App\Modules\Search\Application\Services\SearchIndexerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class IndexEntitySearchDocumentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $entityId,
        public readonly ?string $locale = null,
        public readonly bool $deactivate = false,
    ) {}

    public function handle(SearchIndexerService $indexer): void
    {
        if ($this->deactivate) {
            $indexer->deactivateEntity($this->entityId);

            return;
        }

        $indexer->indexEntity($this->entityId, $this->locale);
    }
}
