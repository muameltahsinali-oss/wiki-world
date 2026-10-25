<?php

declare(strict_types=1);

namespace Tests\Unit\KnowledgeCore;

use App\Modules\KnowledgeCore\Domain\Support\EntityUuidGenerator;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EntityUuidGeneratorTest extends TestCase
{
    #[Test]
    public function it_generates_uuid_v7_strings(): void
    {
        $generator = new EntityUuidGenerator;

        $uuid = $generator->generateString();

        $this->assertTrue(Uuid::isValid($uuid));
        $this->assertSame(7, Uuid::fromString($uuid)->getVersion());
    }

    #[Test]
    public function it_generates_unique_identifiers(): void
    {
        $generator = new EntityUuidGenerator;

        $first = $generator->generateString();
        $second = $generator->generateString();

        $this->assertNotSame($first, $second);
    }
}
