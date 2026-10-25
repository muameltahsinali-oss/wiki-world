<?php

declare(strict_types=1);

namespace Tests\Feature\KnowledgeCore;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class ReferencesMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_references_table_exists_with_required_columns(): void
    {
        $this->assertTrue(Schema::hasTable('references'));
        $this->assertTrue(Schema::hasColumns('references', [
            'source_entity_id',
            'referencer_type',
            'referencer_id',
            'entity_id',
            'page_reference',
        ]));
    }
}
