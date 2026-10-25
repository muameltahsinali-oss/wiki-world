<?php

declare(strict_types=1);

namespace Tests\Feature\KnowledgeCore;

use App\Modules\KnowledgeCore\Domain\Support\EntityUuidGenerator;
use Database\Seeders\EntityTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class PersonProfilesMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_profiles_table_has_hijri_date_fields(): void
    {
        $this->assertTrue(Schema::hasTable('person_profiles'));
        $this->assertTrue(Schema::hasColumns('person_profiles', [
            'entity_id',
            'birth_date_hijri',
            'death_date_hijri',
            'is_masum',
            'ahlulbayt_generation',
            'imam_order',
        ]));
    }

    public function test_person_profile_links_to_entity(): void
    {
        $this->seed(EntityTypeSeeder::class);

        $entityId = $this->createEntity();

        DB::table('person_profiles')->insert([
            'entity_id' => $entityId,
            'birth_date_hijri' => '23 BH',
            'is_masum' => true,
            'ahlulbayt_generation' => 1,
            'attributes' => '{}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('person_profiles', [
            'entity_id' => $entityId,
            'is_masum' => true,
        ]);
    }

    private function createEntity(): int
    {
        $entityTypeId = DB::table('entity_types')->where('code', 'person')->value('id');

        return (int) DB::table('entities')->insertGetId([
            'uuid' => (new EntityUuidGenerator)->generateString(),
            'entity_type_id' => $entityTypeId,
            'workflow_state' => 'draft',
            'module_flags' => '{}',
            'is_indexable' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
