<?php

declare(strict_types=1);

namespace Tests\Feature\KnowledgeCore;

use App\Modules\KnowledgeCore\Domain\Support\EntityUuidGenerator;
use Database\Seeders\EntityTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class EntitiesMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_entities_table_has_required_columns(): void
    {
        $this->assertTrue(Schema::hasTable('entities'));
        $this->assertTrue(Schema::hasColumns('entities', [
            'id',
            'uuid',
            'entity_type_id',
            'workflow_state',
            'module_flags',
            'is_indexable',
            'published_at',
            'published_version_id',
            'created_by_user_id',
            'updated_by_user_id',
            'deleted_at',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_entity_can_be_inserted_with_foreign_keys(): void
    {
        $this->seed(EntityTypeSeeder::class);

        $entityTypeId = \DB::table('entity_types')->where('code', 'person')->value('id');

        \DB::table('entities')->insert([
            'uuid' => (new EntityUuidGenerator)->generateString(),
            'entity_type_id' => $entityTypeId,
            'workflow_state' => 'draft',
            'module_flags' => '{}',
            'is_indexable' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseCount('entities', 1);
    }
}
