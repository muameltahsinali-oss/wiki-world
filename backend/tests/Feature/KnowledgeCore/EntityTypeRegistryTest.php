<?php

declare(strict_types=1);

namespace Tests\Feature\KnowledgeCore;

use App\Modules\KnowledgeCore\Application\Services\EntityTypeRegistry;
use Database\Seeders\EntityTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EntityTypeRegistryTest extends TestCase
{
    use RefreshDatabase;

    private EntityTypeRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EntityTypeSeeder::class);
        $this->registry = $this->app->make(EntityTypeRegistry::class);
    }

    public function test_resolves_known_entity_type(): void
    {
        $type = $this->registry->getByCode('person');

        $this->assertSame('person', $type->code);
        $this->assertSame('person_profiles', $type->profileTable);
        $this->assertTrue($type->isMvp);
    }

    public function test_unknown_entity_type_returns_null(): void
    {
        $this->assertNull($this->registry->findByCode('unknown_type'));
    }
}
