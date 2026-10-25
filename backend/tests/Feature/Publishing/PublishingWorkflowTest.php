<?php

declare(strict_types=1);

namespace Tests\Feature\Publishing;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use Illuminate\Support\Facades\DB;
use Tests\Support\EditorialTestHelpers;
use Tests\TestCase;

final class PublishingWorkflowTest extends TestCase
{
    use EditorialTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedKnowledgeBase();
        $this->setUpEditorialHttp();
    }

    public function test_full_workflow_submit_approve_publish_unpublish_archive(): void
    {
        [$editor, $editorToken] = $this->editorWithDomainScope('ahlulbayt');
        [$senior] = $this->seniorEditorWithDomainScope('ahlulbayt');

        $uuid = $this->createDraftEntity($editorToken, ['slug' => 'workflow-'.uniqid()]);

        $this->saveEntityDraft($editorToken, $uuid, [
            'title' => 'سير العمل',
            'blocks' => [['type' => 'paragraph', 'content' => 'محتوى للمراجعة']],
        ])->assertOk();

        $this->addEntityReference($uuid);

        $submit = $this->postJsonAs($editor, '/api/v1/editorial/entities/'.$uuid.'/submit');
        $submit->assertOk()
            ->assertJsonPath('data.workflow_state', 'in_review');

        $submissionId = (int) $submit->json('data.submission_id');
        $this->assertDatabaseHas('editorial_submissions', ['id' => $submissionId]);

        $this->postJsonAs($senior, '/api/v1/editorial/submissions/'.$submissionId.'/review', [
            'decision' => 'approve',
            'notes' => 'Looks good',
        ])
            ->assertOk()
            ->assertJsonPath('data.workflow_state', 'approved');

        $this->assertDatabaseHas('editorial_reviews', [
            'submission_id' => $submissionId,
            'decision' => 'approve',
        ]);

        $this->postJsonAs($senior, '/api/v1/editorial/entities/'.$uuid.'/publish', [
            'locale' => 'ar',
        ])
            ->assertOk()
            ->assertJsonPath('data.workflow_state', 'published');

        $this->assertDatabaseHas('entities', [
            'uuid' => $uuid,
            'workflow_state' => 'published',
        ]);

        $this->assertDatabaseHas('publish_events', [
            'entity_id' => Entity::query()->where('uuid', $uuid)->value('id'),
            'action' => 'publish',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'workflow.transition',
            'subject_type' => 'entity',
        ]);

        $this->postJsonAs($senior, '/api/v1/editorial/entities/'.$uuid.'/unpublish')
            ->assertOk()
            ->assertJsonPath('data.workflow_state', 'unpublished');

        $this->postJsonAs($senior, '/api/v1/editorial/entities/'.$uuid.'/archive')
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'INVALID_WORKFLOW_TRANSITION');

        $this->postJsonAs($senior, '/api/v1/editorial/entities/'.$uuid.'/publish', [
            'locale' => 'ar',
        ])->assertStatus(409);

        DB::table('entities')->where('uuid', $uuid)->update(['workflow_state' => 'published']);

        $this->postJsonAs($senior, '/api/v1/editorial/entities/'.$uuid.'/archive')
            ->assertOk()
            ->assertJsonPath('data.workflow_state', 'archived');
    }

    public function test_reject_returns_entity_to_draft(): void
    {
        [$editor, $editorToken] = $this->editorWithDomainScope('ahlulbayt');
        [$senior] = $this->seniorEditorWithDomainScope('ahlulbayt');

        $uuid = $this->createDraftEntity($editorToken, ['slug' => 'reject-'.uniqid()]);

        $this->saveEntityDraft($editorToken, $uuid, [
            'blocks' => [['type' => 'paragraph', 'content' => 'نص']],
        ]);

        $submit = $this->postJsonAs($editor, '/api/v1/editorial/entities/'.$uuid.'/submit')->assertOk();
        $submissionId = (int) $submit->json('data.submission_id');

        $this->postJsonAs($senior, '/api/v1/editorial/submissions/'.$submissionId.'/review', [
            'decision' => 'reject',
            'notes' => 'Needs work',
        ])
            ->assertOk()
            ->assertJsonPath('data.workflow_state', 'draft');
    }

    public function test_publish_from_draft_is_invalid_transition(): void
    {
        [$senior, $seniorToken] = $this->seniorEditorWithDomainScope('ahlulbayt');
        $uuid = $this->createDraftEntity($seniorToken, ['slug' => 'invalid-publish-'.uniqid()]);

        $this->postJsonAs($senior, '/api/v1/editorial/entities/'.$uuid.'/publish', [
            'locale' => 'ar',
        ])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'INVALID_WORKFLOW_TRANSITION');
    }

    public function test_submit_without_version_fails(): void
    {
        [$editor, $editorToken] = $this->editorWithDomainScope('ahlulbayt');
        $uuid = $this->createDraftEntity($editorToken, ['slug' => 'no-version-'.uniqid()]);

        $this->postJsonAs($editor, '/api/v1/editorial/entities/'.$uuid.'/submit')
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'INVALID_ARGUMENT');
    }

    public function test_version_list_restore_preview_and_readiness(): void
    {
        [$editor, $editorToken] = $this->editorWithDomainScope('ahlulbayt');
        $uuid = $this->createDraftEntity($editorToken, ['slug' => 'versions-'.uniqid()]);

        $this->saveEntityDraft($editorToken, $uuid, [
            'title' => 'الإصدار الأول',
            'blocks' => [['type' => 'paragraph', 'content' => 'نص قديم']],
        ])->assertOk();

        $this->saveEntityDraft($editorToken, $uuid, [
            'title' => 'الإصدار الثاني',
            'blocks' => [['type' => 'paragraph', 'content' => 'نص جديد']],
        ])->assertOk();

        $versions = $this->getJsonAs($editor, '/api/v1/editorial/entities/'.$uuid.'/versions')
            ->assertOk()
            ->json('data');

        $this->assertCount(2, $versions);
        $this->assertSame(2, $versions[0]['version_number']);

        $this->getJsonAs($editor, '/api/v1/editorial/entities/'.$uuid.'/versions/1')
            ->assertOk()
            ->assertJsonPath('data.snapshot.title', 'الإصدار الأول');

        $this->getJsonAs($editor, '/api/v1/editorial/entities/'.$uuid.'/preview?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.title', 'الإصدار الثاني')
            ->assertJsonPath('data.type', 'person');

        $readiness = $this->getJsonAs($editor, '/api/v1/editorial/entities/'.$uuid.'/publish-readiness?locale=ar')
            ->assertOk()
            ->json('data');

        $this->assertFalse($readiness['ready']);
        $this->assertFalse(collect($readiness['checks'])->firstWhere('code', 'min_sources')['passed']);

        $this->addEntityReference($uuid);

        $this->getJsonAs($editor, '/api/v1/editorial/entities/'.$uuid.'/publish-readiness?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.ready', true);

        $this->postJsonAs($editor, '/api/v1/editorial/entities/'.$uuid.'/versions/1/restore')
            ->assertOk()
            ->assertJsonPath('data.title', 'الإصدار الأول');

        $this->getJsonAs($editor, '/api/v1/editorial/entities/'.$uuid.'/versions')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_preview_denied_without_editorial_access(): void
    {
        [$user] = $this->verifiedUserToken('registered');
        [$editor, $editorToken] = $this->editorWithDomainScope('ahlulbayt');
        $uuid = $this->createDraftEntity($editorToken, ['slug' => 'preview-deny-'.uniqid()]);

        $this->getJsonAs($user, '/api/v1/editorial/entities/'.$uuid.'/preview?locale=ar')
            ->assertStatus(403);
    }
}
