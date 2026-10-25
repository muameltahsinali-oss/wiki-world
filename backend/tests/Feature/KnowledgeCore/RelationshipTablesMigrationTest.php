<?php

declare(strict_types=1);

namespace Tests\Feature\KnowledgeCore;

use Database\Seeders\RelationshipTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class RelationshipTablesMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_relationship_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('relationship_types'));
        $this->assertTrue(Schema::hasTable('relationship_type_allowed_pairs'));
        $this->assertTrue(Schema::hasTable('entity_relationships'));
    }

    public function test_relationship_type_seeder_registers_nineteen_types(): void
    {
        $this->seed(RelationshipTypeSeeder::class);

        $this->assertDatabaseCount('relationship_types', count(RelationshipTypeSeeder::definitions()));
        $this->assertDatabaseHas('relationship_types', [
            'code' => 'participated_in',
            'label_en' => 'participated in',
        ]);
    }
}
