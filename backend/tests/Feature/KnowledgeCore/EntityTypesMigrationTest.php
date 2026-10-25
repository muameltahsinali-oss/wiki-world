<?php

declare(strict_types=1);

namespace Tests\Feature\KnowledgeCore;

use Database\Seeders\EntityTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class EntityTypesMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_entity_types_table_has_required_columns(): void
    {
        $this->assertTrue(Schema::hasTable('entity_types'));
        $this->assertTrue(Schema::hasColumns('entity_types', [
            'id',
            'code',
            'class',
            'profile_table',
            'primary_domain',
            'is_mvp',
            'is_active',
            'sort_order',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_entity_type_seeder_registers_mvp_ontology(): void
    {
        $this->seed(EntityTypeSeeder::class);

        $this->assertDatabaseCount('entity_types', count(EntityTypeSeeder::definitions()));
        $this->assertDatabaseHas('entity_types', [
            'code' => 'person',
            'class' => 'canonical',
            'profile_table' => 'person_profiles',
            'primary_domain' => 'ahlulbayt',
            'is_mvp' => true,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('entity_types', [
            'code' => 'verse',
            'class' => 'canonical',
            'profile_table' => 'quran_verses',
            'primary_domain' => 'quran',
            'is_mvp' => true,
        ]);
        $this->assertGreaterThanOrEqual(8, $this->countMvpTypes());
    }

    public function test_entity_type_codes_are_unique(): void
    {
        $this->seed(EntityTypeSeeder::class);

        $codes = EntityTypeSeeder::definitions();
        $uniqueCodes = array_unique(array_column($codes, 'code'));

        $this->assertCount(count($codes), $uniqueCodes);
    }

    private function countMvpTypes(): int
    {
        return (int) DB::table('entity_types')->where('is_mvp', true)->count();
    }
}
