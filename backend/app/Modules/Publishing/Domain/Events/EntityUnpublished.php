<?php

declare(strict_types=1);

namespace App\Modules\Publishing\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class EntityUnpublished
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $entityUuid,
        public readonly int $entityId,
        public readonly int $performedByUserId,
    ) {}
}
