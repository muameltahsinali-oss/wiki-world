<?php

declare(strict_types=1);

namespace Tests\Feature\KnowledgeCore;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class MediaTablesMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_tables_exist(): void
    {
        foreach (['media_files', 'media_variants', 'entity_media'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }
    }

    public function test_entity_localizations_has_og_image_column(): void
    {
        $this->assertTrue(
            Schema::hasColumn('entity_localizations', 'og_image_media_id'),
        );
    }
}
