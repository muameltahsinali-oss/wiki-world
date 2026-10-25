<?php

declare(strict_types=1);

namespace Tests\Feature\AI;

use App\Modules\Search\Infrastructure\Models\SearchDocument;
use App\Modules\Shared\Application\Contracts\RateLimiterGatewayInterface;
use Illuminate\Support\Facades\DB;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Support\EditorialTestHelpers;
use Tests\TestCase;

final class AiChatApiTest extends TestCase
{
    use EditorialTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedKnowledgeBase();
        $this->setUpEditorialHttp();
        $this->seedCorpusDocument();
    }

    public function test_chat_returns_citations_via_fake_llm(): void
    {
        $response = $this->postJson('/api/v1/ai/chat', [
            'message' => 'What is Ghadir Khumm significance?',
            'locale' => 'ar',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.disclaimer', 'This is educational content, not a religious ruling.')
            ->assertJsonStructure([
                'data' => [
                    'conversation_uuid',
                    'message_uuid',
                    'answer',
                    'citations' => [
                        ['entity_uuid', 'title'],
                    ],
                ],
                'meta' => ['model', 'tokens_used'],
            ]);

        $this->assertNotEmpty($response->json('data.citations'));
        $this->assertFalse((bool) $response->json('data.refused'));
    }

    public function test_fatwa_request_is_refused(): void
    {
        $response = $this->postJson('/api/v1/ai/chat', [
            'message' => 'Is it haram to do this? Need a fatwa please.',
            'locale' => 'en',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.refused', true)
            ->assertJsonPath('data.citations', []);

        $this->assertStringContainsString('cannot provide fatwas', (string) $response->json('data.answer'));
    }

    public function test_rate_limit_uses_gateway(): void
    {
        $gateway = Mockery::mock(RateLimiterGatewayInterface::class);
        $gateway->shouldReceive('tooManyAttempts')->once()->andReturn(true);
        $gateway->shouldReceive('hit')->never();
        $this->app->instance(RateLimiterGatewayInterface::class, $gateway);

        $this->postJson('/api/v1/ai/chat', [
            'message' => 'Tell me about Ghadir',
            'locale' => 'ar',
        ])
            ->assertStatus(429)
            ->assertJsonPath('error.code', 'RATE_LIMIT_EXCEEDED');
    }

    public function test_conversation_list_for_authenticated_user(): void
    {
        [$user] = $this->verifiedUserToken('registered');

        $chat = $this->postJsonAs($user, '/api/v1/ai/chat', [
            'message' => 'Tell me about Ghadir Khumm',
            'locale' => 'ar',
        ]);

        $chat->assertOk();
        $conversationUuid = $chat->json('data.conversation_uuid');
        $this->assertNotEmpty($conversationUuid);

        $this->getJsonAs($user, '/api/v1/me/ai/conversations')
            ->assertOk()
            ->assertJsonFragment(['uuid' => $conversationUuid]);

        $this->getJsonAs($user, '/api/v1/me/ai/conversations/'.$conversationUuid)
            ->assertOk()
            ->assertJsonPath('data.uuid', $conversationUuid)
            ->assertJsonStructure([
                'data' => [
                    'messages' => [
                        ['uuid', 'role', 'content'],
                    ],
                ],
            ]);
    }

    private function seedCorpusDocument(): void
    {
        $languageId = (int) DB::table('languages')->where('code', 'ar')->value('id');
        $typeId = (int) DB::table('entity_types')->where('code', 'event')->value('id');
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
            'title' => 'Event of Ghadir Khumm',
            'slug' => 'ghadir-khumm-'.uniqid(),
            'summary' => 'Historical event at Ghadir',
            'translation_status' => 'published',
            'workflow_state' => 'published',
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        SearchDocument::query()->create([
            'entity_id' => $entityId,
            'language_id' => $languageId,
            'entity_type_code' => 'event',
            'title' => 'Event of Ghadir Khumm',
            'body_text' => 'The significance of Ghadir Khumm in Islamic history',
            'module_flags' => ['ahlulbayt' => true],
            'facets' => ['type' => 'event', 'module' => 'ahlulbayt'],
            'popularity_score' => 20,
            'meilisearch_id' => $uuid.':ar',
            'is_active' => true,
            'indexed_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
