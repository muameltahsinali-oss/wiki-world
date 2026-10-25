<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use Tests\Support\EditorialTestHelpers;
use Tests\TestCase;

final class IdorProtectionTest extends TestCase
{
    use EditorialTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedKnowledgeBase();
        $this->setUpEditorialHttp();
    }

    public function test_draft_entity_uuid_returns_404_on_public_api(): void
    {
        [$editor, $token] = $this->editorWithDomainScope('ahlulbayt');
        $uuid = $this->createDraftEntity($token, ['slug' => 'secret-draft-'.uniqid()]);

        $this->getJson('/api/v1/ar/persons/'.$uuid)
            ->assertNotFound();

        $this->getJson('/api/v1/en/persons/'.$uuid)
            ->assertNotFound();
    }

    public function test_unpublished_entity_is_not_listed_publicly(): void
    {
        [$editor, $token] = $this->editorWithDomainScope('ahlulbayt');
        $uuid = $this->createDraftEntity($token, ['slug' => 'hidden-'.uniqid()]);

        $this->saveEntityDraft($token, $uuid, [
            'title' => 'مسودة سرية',
            'blocks' => [['type' => 'paragraph', 'content' => 'محتوى خاص']],
        ])->assertOk();

        $listed = collect($this->getJson('/api/v1/ar/persons')->json('data'))
            ->pluck('uuid');

        $this->assertFalse($listed->contains($uuid));
    }

    public function test_other_users_ai_conversation_returns_404(): void
    {
        $this->seedAiCorpus();

        [$userA] = $this->verifiedUserToken('registered');
        [$userB] = $this->verifiedUserToken('registered');

        $chat = $this->postJsonAs($userA, '/api/v1/ai/chat', [
            'message' => 'Tell me about Ghadir',
            'locale' => 'ar',
        ]);

        $chat->assertOk();
        $conversationUuid = (string) $chat->json('data.conversation_uuid');
        $this->assertNotEmpty($conversationUuid);

        $this->getJsonAs($userB, '/api/v1/me/ai/conversations/'.$conversationUuid)
            ->assertNotFound();
    }

    private function seedAiCorpus(): void
    {
        $languageId = (int) \Illuminate\Support\Facades\DB::table('languages')->where('code', 'ar')->value('id');
        $typeId = (int) \Illuminate\Support\Facades\DB::table('entity_types')->where('code', 'event')->value('id');
        $uuid = \Ramsey\Uuid\Uuid::uuid7()->toString();

        $entityId = (int) \Illuminate\Support\Facades\DB::table('entities')->insertGetId([
            'uuid' => $uuid,
            'entity_type_id' => $typeId,
            'workflow_state' => 'published',
            'module_flags' => json_encode(['ahlulbayt' => true]),
            'is_indexable' => true,
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::table('entity_localizations')->insert([
            'entity_id' => $entityId,
            'language_id' => $languageId,
            'title' => 'غدير خم',
            'slug' => 'ghadir-'.uniqid(),
            'summary' => 'حدث غدير خم',
            'workflow_state' => 'published',
            'translation_status' => 'published',
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \App\Modules\Search\Infrastructure\Models\SearchDocument::query()->create([
            'entity_id' => $entityId,
            'language_id' => $languageId,
            'entity_type_code' => 'event',
            'title' => 'غدير خم',
            'body_text' => 'حدث غدير خم وتنصيب أمير المؤمنين',
            'module_flags' => ['ahlulbayt' => true],
            'facets' => ['type' => 'event'],
            'popularity_score' => 20,
            'meilisearch_id' => $uuid.':ar',
            'is_active' => true,
            'indexed_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
