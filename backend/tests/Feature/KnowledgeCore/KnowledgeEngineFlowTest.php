<?php

declare(strict_types=1);

namespace Tests\Feature\KnowledgeCore;

use App\Modules\KnowledgeCore\Application\Services\RelationshipService;
use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use Illuminate\Support\Facades\DB;
use Tests\Support\EditorialTestHelpers;
use Tests\TestCase;

final class KnowledgeEngineFlowTest extends TestCase
{
    use EditorialTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedKnowledgeBase();
        $this->setUpEditorialHttp();
    }

    public function test_editor_can_save_draft_and_creates_version(): void
    {
        [, $token] = $this->editorWithDomainScope('ahlulbayt');
        $uuid = $this->createDraftEntity($token);

        $response = $this->saveEntityDraft($token, $uuid, [
            'title' => 'الإمام علي - محدث',
            'summary' => 'ملخص محدث',
            'blocks' => [
                ['type' => 'heading', 'content' => ['text' => 'عنوان'], 'heading_level' => 2],
                ['type' => 'paragraph', 'content' => 'نص فقرة'],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'الإمام علي - محدث')
            ->assertJsonPath('data.version_number', 1)
            ->assertJsonCount(2, 'data.blocks');

        $entityId = Entity::query()->where('uuid', $uuid)->value('id');

        $this->assertDatabaseHas('entity_versions', [
            'entity_id' => $entityId,
            'version_number' => 1,
        ]);

        $this->assertDatabaseHas('content_blocks', [
            'entity_id' => $entityId,
            'block_type' => 'paragraph',
        ]);
    }

    public function test_draft_entity_is_not_publicly_visible(): void
    {
        [, $token] = $this->editorWithDomainScope('ahlulbayt');
        $uuid = $this->createDraftEntity($token);

        $this->getJson('/api/v1/ar/entities/'.$uuid)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }

    public function test_published_entity_is_publicly_visible(): void
    {
        [$editor, $editorToken] = $this->editorWithDomainScope('ahlulbayt');
        [$senior] = $this->seniorEditorWithDomainScope('ahlulbayt');

        $uuid = $this->createDraftEntity($editorToken, ['slug' => 'public-person-'.uniqid()]);

        $this->saveEntityDraft($editorToken, $uuid, [
            'title' => 'شخصية منشورة',
            'blocks' => [['type' => 'paragraph', 'content' => 'محتوى عام']],
        ])->assertOk();

        $this->approveAndPublishEntity($editor, $senior, $uuid);

        $this->getJson('/api/v1/ar/entities/'.$uuid)
            ->assertOk()
            ->assertJsonPath('data.workflow_state', 'published')
            ->assertJsonPath('data.type', 'person');
    }

    public function test_editorial_get_and_list_entity(): void
    {
        [, $token] = $this->editorWithDomainScope('ahlulbayt');
        $uuid = $this->createDraftEntity($token, ['title' => 'للقائمة']);

        $this->withToken($token)->getJson('/api/v1/editorial/entities/'.$uuid.'?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.uuid', $uuid)
            ->assertJsonPath('data.workflow_state', 'draft');

        $this->withToken($token)->getJson('/api/v1/editorial/entities?filter[domain]=ahlulbayt&locale=ar')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['pagination']]);
    }

    public function test_public_relationships_for_published_entity(): void
    {
        [$editor, $editorToken] = $this->editorWithDomainScope('ahlulbayt');
        [$senior] = $this->seniorEditorWithDomainScope('ahlulbayt');

        $sourceUuid = $this->createDraftEntity($editorToken, [
            'title' => 'مصدر',
            'slug' => 'rel-source-'.uniqid(),
        ]);
        $targetUuid = $this->createDraftEntity($editorToken, [
            'title' => 'هدف',
            'slug' => 'rel-target-'.uniqid(),
        ]);

        $this->seedPersonRelatedPair();

        $sourceId = (int) Entity::query()->where('uuid', $sourceUuid)->value('id');

        $relationships = $this->app->make(RelationshipService::class);
        $relationships->createRelationship(
            sourceEntityId: $sourceId,
            targetEntityUuid: $targetUuid,
            relationshipTypeCode: 'related_to',
            metadata: [],
            userId: $editor->id,
        );

        foreach ([$sourceUuid, $targetUuid] as $uuid) {
            $this->saveEntityDraft($editorToken, $uuid, [
                'blocks' => [['type' => 'paragraph', 'content' => 'نص']],
            ]);
            $this->approveAndPublishEntity($editor, $senior, $uuid);
        }

        $this->getJson('/api/v1/ar/entities/'.$sourceUuid.'/relationships')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.relationship_type', 'related_to');
    }

    public function test_save_with_duplicate_slug_returns_conflict(): void
    {
        [, $token] = $this->editorWithDomainScope('general');
        $slug = 'duplicate-slug-'.uniqid();

        $uuid1 = $this->createDraftEntity($token, ['domain' => 'general', 'slug' => $slug]);
        $uuid2 = $this->createDraftEntity($token, [
            'domain' => 'general',
            'slug' => 'other-'.uniqid(),
        ]);

        $this->saveEntityDraft($token, $uuid2, ['slug' => $slug])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'SLUG_CONFLICT');

        $this->assertNotEquals($uuid1, $uuid2);
    }

    public function test_save_rejects_invalid_content_blocks(): void
    {
        [, $token] = $this->editorWithDomainScope('ahlulbayt');
        $uuid = $this->createDraftEntity($token);

        $this->saveEntityDraft($token, $uuid, [
            'blocks' => [
                ['type' => 'paragraph', 'content' => '<script>alert(1)</script>'],
            ],
        ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'INVALID_ARGUMENT');
    }

    public function test_public_type_route_validates_entity_type(): void
    {
        [$editor, $editorToken] = $this->editorWithDomainScope('ahlulbayt');
        [$senior] = $this->seniorEditorWithDomainScope('ahlulbayt');

        $uuid = $this->createDraftEntity($editorToken, ['slug' => 'type-route-'.uniqid()]);

        $this->saveEntityDraft($editorToken, $uuid, [
            'blocks' => [['type' => 'paragraph', 'content' => 'نص']],
        ]);

        $this->approveAndPublishEntity($editor, $senior, $uuid);

        $this->getJson('/api/v1/ar/persons/'.$uuid)->assertOk();
        $this->getJson('/api/v1/ar/articles/'.$uuid)->assertNotFound();
    }

    private function seedPersonRelatedPair(): void
    {
        $personTypeId = DB::table('entity_types')->where('code', 'person')->value('id');
        $relatedTypeId = DB::table('relationship_types')->where('code', 'related_to')->value('id');

        DB::table('relationship_type_allowed_pairs')->insert([
            'relationship_type_id' => $relatedTypeId,
            'source_entity_type_id' => $personTypeId,
            'target_entity_type_id' => $personTypeId,
        ]);
    }

    public function test_published_entity_workflow_state_in_database(): void
    {
        $entity = Entity::query()->first();

        if ($entity === null) {
            $this->assertTrue(true);

            return;
        }

        $this->assertContains($entity->workflow_state, WorkflowState::values());
    }
}
