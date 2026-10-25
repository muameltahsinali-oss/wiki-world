<?php

declare(strict_types=1);

namespace Tests\Feature\Ahlulbayt;

use Database\Seeders\AhlulbaytLaunchSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Support\EditorialTestHelpers;
use Tests\TestCase;

final class AhlulbaytApiTest extends TestCase
{
    use EditorialTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedKnowledgeBase();
        $this->seed(AhlulbaytLaunchSeeder::class);
        $this->setUpEditorialHttp();
    }

    public function test_landing_returns_stats_and_featured_content(): void
    {
        $this->getJson('/api/v1/ar/ahlulbayt')
            ->assertOk()
            ->assertJsonPath('data.stats.masumin_count', 14)
            ->assertJsonStructure([
                'data' => [
                    'title',
                    'stats',
                    'featured_persons',
                    'featured_events',
                    'timeline_preview',
                ],
            ]);
    }

    public function test_browse_lists_ahlulbayt_entities(): void
    {
        $this->getJson('/api/v1/ar/ahlulbayt/browse?filter[type]=person')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['pagination']]);
    }

    public function test_lineage_returns_ancestors_and_descendants(): void
    {
        $uuid = (string) DB::table('entities')
            ->join('entity_localizations', 'entities.id', '=', 'entity_localizations.entity_id')
            ->where('entity_localizations.slug', 'imam-hussein')
            ->value('entities.uuid');

        $this->getJson('/api/v1/ar/ahlulbayt/persons/'.$uuid.'/lineage')
            ->assertOk()
            ->assertJsonStructure(['data' => ['ancestors', 'descendants']]);
    }

    public function test_genealogy_returns_tree(): void
    {
        $uuid = (string) DB::table('entities')
            ->join('entity_localizations', 'entities.id', '=', 'entity_localizations.entity_id')
            ->where('entity_localizations.slug', 'prophet-muhammad')
            ->value('entities.uuid');

        $this->getJson('/api/v1/ar/ahlulbayt/persons/'.$uuid.'/genealogy')
            ->assertOk()
            ->assertJsonStructure(['data' => ['root', 'nodes', 'edges']]);
    }

    public function test_timeline_lists_events(): void
    {
        $this->getJson('/api/v1/ar/ahlulbayt/timeline')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
