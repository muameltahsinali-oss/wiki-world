<?php

declare(strict_types=1);

namespace Tests\Feature\Search;

use App\Modules\Search\Infrastructure\Models\SearchDocument;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Tests\Support\EditorialTestHelpers;
use Tests\TestCase;

final class SearchApiTest extends TestCase
{
    use EditorialTestHelpers;

    private string $entityUuid;

    private int $entityId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedKnowledgeBase();
        $this->setUpEditorialHttp();
        $this->seedSearchCorpus();
    }

    public function test_search_returns_arabic_hits(): void
    {
        $response = $this->getJson('/api/v1/ar/search?q='.urlencode('غدير'));

        $response->assertOk()
            ->assertJsonPath('meta.query', 'غدير')
            ->assertJsonStructure([
                'data' => [
                    ['uuid', 'type', 'title', 'slug', 'module'],
                ],
                'meta' => ['total', 'took_ms', 'facets', 'pagination'],
            ]);

        $this->assertGreaterThanOrEqual(1, (int) $response->json('meta.total'));
        $this->assertSame($this->entityUuid, $response->json('data.0.uuid'));
    }

    public function test_suggestions_return_titles(): void
    {
        $response = $this->getJson('/api/v1/ar/search/suggestions?q='.urlencode('غد'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['text', 'type'],
                ],
            ]);

        $this->assertStringContainsString('غدير', (string) $response->json('data.0.text'));
    }

    public function test_related_returns_results(): void
    {
        $response = $this->getJson('/api/v1/ar/search/related/'.$this->entityUuid);

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_quran_scoped_search(): void
    {
        $response = $this->getJson('/api/v1/ar/quran/search?q='.urlencode('سورة'));

        $response->assertOk();
        $this->assertGreaterThanOrEqual(1, (int) $response->json('meta.total'));
        $this->assertSame('quran', $response->json('data.0.module'));
    }

    private function seedSearchCorpus(): void
    {
        $languageId = (int) DB::table('languages')->where('code', 'ar')->value('id');
        $personTypeId = (int) DB::table('entity_types')->where('code', 'person')->value('id');
        $articleTypeId = (int) DB::table('entity_types')->where('code', 'article')->value('id');

        $this->entityUuid = Uuid::uuid7()->toString();
        $this->entityId = (int) DB::table('entities')->insertGetId([
            'uuid' => $this->entityUuid,
            'entity_type_id' => $personTypeId,
            'workflow_state' => 'published',
            'module_flags' => json_encode(['ahlulbayt' => true, 'general' => true]),
            'is_indexable' => true,
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('entity_localizations')->insert([
            'entity_id' => $this->entityId,
            'language_id' => $languageId,
            'title' => 'حدث الغدير',
            'slug' => 'ghadir-'.uniqid(),
            'summary' => 'واقعة غدير خم التاريخية',
            'translation_status' => 'published',
            'workflow_state' => 'published',
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        SearchDocument::query()->create([
            'entity_id' => $this->entityId,
            'language_id' => $languageId,
            'entity_type_code' => 'person',
            'title' => 'حدث الغدير',
            'body_text' => 'واقعة غدير خم التاريخية وأهميتها',
            'module_flags' => ['ahlulbayt' => true, 'general' => true],
            'facets' => ['type' => 'person', 'module' => 'ahlulbayt'],
            'popularity_score' => 10,
            'meilisearch_id' => $this->entityUuid.':ar',
            'is_active' => true,
            'indexed_at' => now(),
            'updated_at' => now(),
        ]);

        $quranUuid = Uuid::uuid7()->toString();
        $quranEntityId = (int) DB::table('entities')->insertGetId([
            'uuid' => $quranUuid,
            'entity_type_id' => $articleTypeId,
            'workflow_state' => 'published',
            'module_flags' => json_encode(['quran' => true]),
            'is_indexable' => true,
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('entity_localizations')->insert([
            'entity_id' => $quranEntityId,
            'language_id' => $languageId,
            'title' => 'سورة الفاتحة',
            'slug' => 'fatiha-'.uniqid(),
            'summary' => 'مقدمة المصحف',
            'translation_status' => 'published',
            'workflow_state' => 'published',
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        SearchDocument::query()->create([
            'entity_id' => $quranEntityId,
            'language_id' => $languageId,
            'entity_type_code' => 'article',
            'title' => 'سورة الفاتحة',
            'body_text' => 'سورة أم الكتاب',
            'module_flags' => ['quran' => true],
            'facets' => ['type' => 'article', 'module' => 'quran'],
            'popularity_score' => 5,
            'meilisearch_id' => $quranUuid.':ar',
            'is_active' => true,
            'indexed_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
