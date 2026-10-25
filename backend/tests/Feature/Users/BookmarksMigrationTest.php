<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class BookmarksMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bookmarks_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('bookmarks'));
        $this->assertTrue(Schema::hasColumns('bookmarks', [
            'id',
            'user_id',
            'entity_id',
            'created_at',
        ]));
    }
}
