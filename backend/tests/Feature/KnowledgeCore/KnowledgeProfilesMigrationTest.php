<?php

declare(strict_types=1);

namespace Tests\Feature\KnowledgeCore;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class KnowledgeProfilesMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mvp_knowledge_profile_tables_exist(): void
    {
        foreach (['event_profiles', 'place_profiles', 'concept_profiles', 'source_profiles', 'book_profiles'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }
    }
}
