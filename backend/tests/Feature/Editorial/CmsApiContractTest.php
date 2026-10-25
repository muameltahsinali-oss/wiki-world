<?php

declare(strict_types=1);

namespace Tests\Feature\Editorial;

use App\Models\User;
use App\Modules\RolesPermissions\Application\Services\RoleResolver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\Support\EditorialTestHelpers;
use Tests\TestCase;

final class CmsApiContractTest extends TestCase
{
    use EditorialTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedKnowledgeBase();
        $this->setUpEditorialHttp();
        Storage::fake('local');
    }

    public function test_editorial_insight_endpoints(): void
    {
        [$editor] = $this->editorWithDomainScope('ahlulbayt');

        $this->getJsonAs($editor, '/api/v1/editorial/dashboard')->assertOk();
        $this->getJsonAs($editor, '/api/v1/editorial/drafts')->assertOk();
        $this->getJsonAs($editor, '/api/v1/editorial/submissions')->assertOk();
        $this->getJsonAs($editor, '/api/v1/editorial/reviews/history')->assertOk();
        $this->getJsonAs($editor, '/api/v1/editorial/activity')->assertOk();
        $this->getJsonAs($editor, '/api/v1/editorial/search/entities?q=test')->assertOk();
        $this->getJsonAs($editor, '/api/v1/editorial/settings')->assertOk();
        $this->getJsonAs($editor, '/api/v1/editorial/translations/queue')->assertOk();
        $this->getJsonAs($editor, '/api/v1/editorial/analytics')->assertOk();
    }

    public function test_entity_seo_endpoints(): void
    {
        [$editor, $token] = $this->editorWithDomainScope('ahlulbayt');
        $uuid = $this->createDraftEntity($token, ['slug' => 'seo-'.uniqid()]);

        $this->getJsonAs($editor, '/api/v1/editorial/entities/'.$uuid.'/seo?locale=ar')
            ->assertOk();

        $this->patchJsonAs($editor, '/api/v1/editorial/entities/'.$uuid.'/seo', [
            'locale' => 'ar',
            'meta_title' => 'عنوان SEO',
            'meta_description' => 'وصف',
        ])->assertOk()
            ->assertJsonPath('data.meta_title', 'عنوان SEO');
    }

    public function test_media_endpoints(): void
    {
        [$editor] = $this->editorWithDomainScope('ahlulbayt');
        $file = UploadedFile::fake()->image('photo.jpg');
        $abilities = $this->app->make(RoleResolver::class)
            ->tokenAbilitiesForUser($editor);
        Sanctum::actingAs($editor, $abilities, 'sanctum');

        $upload = $this->post('/api/v1/editorial/media', [
            'file' => $file,
            'alt_text' => 'صورة تجريبية',
        ])->assertCreated();

        $mediaUuid = (string) $upload->json('data.uuid');

        $this->getJsonAs($editor, '/api/v1/editorial/media')->assertOk();
        $this->getJsonAs($editor, '/api/v1/editorial/media/'.$mediaUuid)->assertOk();

        $this->patchJsonAs($editor, '/api/v1/editorial/media/'.$mediaUuid, [
            'alt_text' => 'نص بديل محدّث',
        ])->assertOk();

        $this->postJsonAs($editor, '/api/v1/editorial/media/'.$mediaUuid.'/signed-url')
            ->assertOk()
            ->assertJsonStructure(['data' => ['url', 'expires_at']]);
    }

    public function test_entity_media_attach(): void
    {
        [$editor, $token] = $this->editorWithDomainScope('ahlulbayt');
        $uuid = $this->createDraftEntity($token, ['slug' => 'media-attach-'.uniqid()]);
        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');
        $abilities = $this->app->make(RoleResolver::class)
            ->tokenAbilitiesForUser($editor);
        Sanctum::actingAs($editor, $abilities, 'sanctum');

        $mediaUuid = (string) $this->post('/api/v1/editorial/media', [
            'file' => $file,
        ])->json('data.uuid');

        $this->postJsonAs($editor, '/api/v1/editorial/entities/'.$uuid.'/media', [
            'media_uuid' => $mediaUuid,
            'role' => 'attachment',
        ])->assertOk();
    }

    public function test_personalization_endpoints(): void
    {
        [$user, $token] = $this->verifiedUserToken('registered');
        [$editor, $editorToken] = $this->editorWithDomainScope('ahlulbayt');
        $entityUuid = $this->createDraftEntity($editorToken, ['slug' => 'bookmark-'.uniqid()]);

        $this->getJsonAs($user, '/api/v1/me/bookmarks')->assertOk();

        $this->postJsonAs($user, '/api/v1/me/bookmarks', [
            'entity_uuid' => $entityUuid,
        ])->assertCreated();

        $this->deleteJsonAs($user, '/api/v1/me/bookmarks/'.$entityUuid)->assertNoContent();

        $this->getJsonAs($user, '/api/v1/me/history')->assertOk();
        $this->deleteJsonAs($user, '/api/v1/me/history')->assertNoContent();

        $created = $this->postJsonAs($user, '/api/v1/me/saved-searches', [
            'name' => 'بحثي',
            'query_payload' => ['q' => 'الإمام'],
        ])->assertCreated();

        $searchUuid = (string) $created->json('data.uuid');
        $this->getJsonAs($user, '/api/v1/me/saved-searches')->assertOk();
        $this->deleteJsonAs($user, '/api/v1/me/saved-searches/'.$searchUuid)->assertNoContent();
    }

    public function test_entity_relationship_and_reference_endpoints(): void
    {
        [$editor, $token] = $this->editorWithDomainScope('ahlulbayt');
        $sourceUuid = $this->createDraftEntity($token, ['slug' => 'ref-source-'.uniqid(), 'title' => 'مصدر']);
        $targetUuid = $this->createDraftEntity($token, ['slug' => 'ref-target-'.uniqid(), 'title' => 'هدف']);

        DB::table('relationship_type_allowed_pairs')->insertOrIgnore([
            'relationship_type_id' => DB::table('relationship_types')->where('code', 'related_to')->value('id'),
            'source_entity_type_id' => DB::table('entity_types')->where('code', 'person')->value('id'),
            'target_entity_type_id' => DB::table('entity_types')->where('code', 'person')->value('id'),
        ]);

        $this->getJsonAs($editor, '/api/v1/editorial/entities/'.$sourceUuid.'/relationships')->assertOk();
        $this->getJsonAs($editor, '/api/v1/editorial/entities/'.$sourceUuid.'/references')->assertOk();

        $relationship = $this->postJsonAs($editor, '/api/v1/editorial/entities/'.$sourceUuid.'/relationships', [
            'target_entity_uuid' => $targetUuid,
            'relationship_type' => 'related_to',
        ])->assertCreated();

        $relationshipId = (int) $relationship->json('data.id');

        $reference = $this->postJsonAs($editor, '/api/v1/editorial/entities/'.$sourceUuid.'/references', [
            'source_entity_uuid' => $targetUuid,
            'page_reference' => 'ص 12',
        ])->assertCreated();

        $referenceId = (int) $reference->json('data.id');

        $this->getJsonAs($editor, '/api/v1/editorial/entities/'.$sourceUuid.'/relationships')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJsonAs($editor, '/api/v1/editorial/entities/'.$sourceUuid.'/references')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->deleteJsonAs($editor, '/api/v1/editorial/entities/'.$sourceUuid.'/relationships/'.$relationshipId)
            ->assertNoContent();

        $this->deleteJsonAs($editor, '/api/v1/editorial/entities/'.$sourceUuid.'/references/'.$referenceId)
            ->assertNoContent();
    }

    protected function deleteJsonAs(User $user, string $uri): TestResponse
    {
        $abilities = $this->app->make(RoleResolver::class)
            ->tokenAbilitiesForUser($user);
        Sanctum::actingAs($user, $abilities, 'sanctum');

        return $this->deleteJson($uri);
    }
}
