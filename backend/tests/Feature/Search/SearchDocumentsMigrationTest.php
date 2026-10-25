<?php

declare(strict_types=1);

namespace Tests\Feature\Search;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class SearchDocumentsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_documents_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('search_documents'));
        $this->assertTrue(Schema::hasColumns('search_documents', [
            'id',
            'entity_id',
            'language_id',
            'entity_type_code',
            'title',
            'body_text',
            'module_flags',
            'facets',
            'popularity_score',
            'tsv',
            'meilisearch_id',
            'is_active',
            'indexed_at',
            'updated_at',
        ]));
    }
}
