<?php

declare(strict_types=1);

namespace Tests\Feature\KnowledgeCore;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class PublishingTablesMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishing_tables_exist(): void
    {
        foreach ([
            'entity_versions',
            'editorial_submissions',
            'editorial_reviews',
            'editorial_comments',
            'publish_events',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }
    }

    public function test_entities_has_published_version_foreign_key(): void
    {
        $this->assertTrue(
            Schema::hasColumn('entities', 'published_version_id'),
        );
    }
}
