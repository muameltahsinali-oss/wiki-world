<?php

declare(strict_types=1);

namespace Tests\Feature\KnowledgeCore;

use App\Modules\KnowledgeCore\Domain\Support\EntityUuidGenerator;
use Database\Seeders\EntityTypeSeeder;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class EntityLocalizationsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_languages_and_localizations_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('languages'));
        $this->assertTrue(Schema::hasTable('entity_localizations'));
    }

    public function test_language_seeder_registers_arabic_and_english(): void
    {
        $this->seed(LanguageSeeder::class);

        $this->assertDatabaseHas('languages', [
            'code' => 'ar',
            'is_primary' => true,
            'direction' => 'rtl',
        ]);
        $this->assertDatabaseHas('languages', [
            'code' => 'en',
            'direction' => 'ltr',
        ]);

        $arabicId = DB::table('languages')->where('code', 'ar')->value('id');
        $englishFallback = DB::table('languages')->where('code', 'en')->value('fallback_language_id');

        $this->assertSame($arabicId, $englishFallback);
    }

    public function test_entity_localization_unique_per_entity_and_language(): void
    {
        $this->seed([EntityTypeSeeder::class, LanguageSeeder::class]);

        $entityId = $this->createEntity();
        $languageId = DB::table('languages')->where('code', 'ar')->value('id');

        DB::table('entity_localizations')->insert([
            'entity_id' => $entityId,
            'language_id' => $languageId,
            'title' => 'الإمام علي',
            'slug' => 'imam-ali',
            'translation_status' => 'draft',
            'workflow_state' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseCount('entity_localizations', 1);
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
