<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Domain\Support;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class EntityUuidGenerator
{
    public function generate(): UuidInterface
    {
        return Uuid::uuid7();
    }

    public function generateString(): string
    {
        return $this->generate()->toString();
    }
}
