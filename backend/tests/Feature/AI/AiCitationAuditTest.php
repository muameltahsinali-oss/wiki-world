<?php

declare(strict_types=1);

namespace Tests\Feature\AI;

use App\Modules\Search\Infrastructure\Models\SearchDocument;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Tests\Support\EditorialTestHelpers;
use Tests\TestCase;

final class AiCitationAuditTest extends TestCase
{
    use EditorialTestHelpers;

    private const MIN_CITATION_RATE = 0.95;

    /** @var list<string> */
    private array $questions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedKnowledgeBase();
        $this->setUpEditorialHttp();
        $this->seedCorpusDocuments();
        $this->questions = require __DIR__.'/Fixtures/citation_audit_questions.php';
        config([
            'wikiworld.ai.rate_limit.guest' => 200,
            'wikiworld.ai.rate_limit.registered' => 200,
        ]);
    }

    public function test_citation_audit_meets_prd_threshold(): void
    {
        $this->assertCount(100, $this->questions);

        $withCitations = 0;
        $refused = 0;

        foreach ($this->questions as $question) {
            $response = $this->postJson('/api/v1/ai/chat', [
                'message' => $question,
                'locale' => 'ar',
            ]);

            $response->assertOk();

            if ($response->json('data.refused')) {
                $refused++;

                continue;
            }

            $citations = $response->json('data.citations');
            if (is_array($citations) && count($citations) > 0) {
                $withCitations++;
            }
        }

        $answerable = count($this->questions) - $refused;
        $rate = $answerable > 0 ? $withCitations / $answerable : 0;

        $this->assertGreaterThanOrEqual(
            self::MIN_CITATION_RATE,
            $rate,
            sprintf('Citation rate %.1f%% below PRD threshold (refused: %d)', $rate * 100, $refused),
        );
    }

    private function seedCorpusDocuments(): void
    {
        $languageId = (int) DB::table('languages')->where('code', 'ar')->value('id');
        $typeId = (int) DB::table('entity_types')->where('code', 'event')->value('id');

        $topics = [
            ['title' => 'غدير خم', 'body' => 'حدث غدير خم وتنصيب أمير المؤمنين علي بن أبي طالب'],
            ['title' => 'كربلاء', 'body' => 'واقعة كربلاء واستشهاد الإمام الحسين'],
            ['title' => 'الإمام علي', 'body' => 'سيرة الإمام علي بن أبي طالب'],
            ['title' => 'الإمام الحسين', 'body' => 'سيرة الإمام الحسين بن علي'],
            ['title' => 'فاطمة الزهراء', 'body' => 'سيرة السيدة فاطمة الزهراء'],
        ];

        foreach ($topics as $topic) {
            $uuid = Uuid::uuid7()->toString();
            $entityId = (int) DB::table('entities')->insertGetId([
                'uuid' => $uuid,
                'entity_type_id' => $typeId,
                'workflow_state' => 'published',
                'module_flags' => json_encode(['ahlulbayt' => true]),
                'is_indexable' => true,
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('entity_localizations')->insert([
                'entity_id' => $entityId,
                'language_id' => $languageId,
                'title' => $topic['title'],
                'slug' => str_replace(' ', '-', $topic['title']).'-'.uniqid(),
                'summary' => $topic['body'],
                'workflow_state' => 'published',
                'translation_status' => 'published',
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            SearchDocument::query()->create([
                'entity_id' => $entityId,
                'language_id' => $languageId,
                'entity_type_code' => 'event',
                'title' => $topic['title'],
                'body_text' => $topic['body'],
                'module_flags' => ['ahlulbayt' => true],
                'facets' => ['type' => 'event', 'module' => 'ahlulbayt'],
                'popularity_score' => 10,
                'meilisearch_id' => $uuid.':ar',
                'is_active' => true,
                'indexed_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
