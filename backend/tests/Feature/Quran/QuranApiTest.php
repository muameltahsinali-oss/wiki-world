<?php

declare(strict_types=1);

namespace Tests\Feature\Quran;

use App\Modules\Quran\Domain\QuranVerseCounts;
use Database\Seeders\QuranCorpusSeeder;
use Tests\Support\EditorialTestHelpers;
use Tests\TestCase;

final class QuranApiTest extends TestCase
{
    use EditorialTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedKnowledgeBase();
        $this->seed(QuranCorpusSeeder::class);
        $this->setUpEditorialHttp();
    }

    public function test_lists_114_surahs(): void
    {
        $this->getJson('/api/v1/ar/quran/surahs')
            ->assertOk()
            ->assertJsonCount(114, 'data');
    }

    public function test_get_surah_detail(): void
    {
        $this->getJson('/api/v1/ar/quran/surahs/1')
            ->assertOk()
            ->assertJsonPath('data.number', 1)
            ->assertJsonPath('data.verse_count', 7);
    }

    public function test_get_verse_by_compound_key(): void
    {
        $this->getJson('/api/v1/ar/quran/verses/1:1')
            ->assertOk()
            ->assertJsonPath('data.reference', '1:1')
            ->assertJsonPath('data.surah', 1)
            ->assertJsonPath('data.ayah', 1);
    }

    public function test_lists_surah_verses(): void
    {
        $this->getJson('/api/v1/ar/quran/surahs/1/verses')
            ->assertOk()
            ->assertJsonCount(QuranVerseCounts::VERSES_PER_SURAH[0], 'data');
    }

    public function test_topics_endpoint_returns_list(): void
    {
        $this->getJson('/api/v1/ar/quran/topics')
            ->assertOk()
            ->assertJsonPath('data', []);
    }
}
