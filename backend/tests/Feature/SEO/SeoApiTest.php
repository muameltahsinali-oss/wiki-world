<?php

declare(strict_types=1);

namespace Tests\Feature\SEO;

use App\Modules\SEO\Infrastructure\Models\SitemapEntry;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Tests\Support\EditorialTestHelpers;
use Tests\TestCase;

final class SeoApiTest extends TestCase
{
    use EditorialTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedKnowledgeBase();
        $this->setUpEditorialHttp();
        $this->seedSitemapEntry();
    }

    public function test_sitemap_index_xml(): void
    {
        $response = $this->get('/api/v1/sitemap.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/xml', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<sitemapindex', $response->getContent() ?: '');
        $this->assertStringContainsString('sitemap-ar.xml', $response->getContent() ?: '');
    }

    public function test_locale_sitemap_xml_contains_entries(): void
    {
        $response = $this->get('/api/v1/sitemap-ar.xml');

        $response->assertOk();
        $content = $response->getContent() ?: '';
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString('/ar/persons/', $content);
    }

    public function test_robots_txt(): void
    {
        $response = $this->get('/api/v1/robots.txt');

        $response->assertOk();
        $content = $response->getContent() ?: '';
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Disallow: /api/', $content);
        $this->assertStringContainsString('Sitemap:', $content);
    }

    public function test_admin_redirect_crud(): void
    {
        [$admin] = $this->verifiedUserToken('admin');

        $create = $this->postJsonAs($admin, '/api/v1/admin/redirects', [
            'from_path' => '/ar/old-slug',
            'to_path' => '/ar/new-slug',
            'status' => 301,
            'locale' => 'ar',
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.from_path', '/ar/old-slug')
            ->assertJsonPath('data.redirect_type', 301);

        $id = (int) $create->json('data.id');

        $this->getJsonAs($admin, '/api/v1/admin/redirects')
            ->assertOk()
            ->assertJsonFragment(['from_path' => '/ar/old-slug']);

        $this->deleteJsonAs($admin, '/api/v1/admin/redirects/'.$id)
            ->assertNoContent();

        $this->assertDatabaseMissing('url_redirects', ['id' => $id]);
    }

    public function test_structured_data_generated_on_publish_job(): void
    {
        [$editor, $editorToken] = $this->editorWithDomainScope('ahlulbayt');
        $uuid = $this->createDraftEntity($editorToken, [
            'title' => 'JSON-LD Test',
            'slug' => 'jsonld-test',
        ]);

        $this->saveEntityDraft($editorToken, $uuid, [
            'summary' => 'Summary for SEO',
            'body' => [['type' => 'paragraph', 'content' => 'Body text']],
        ]);

        [$senior] = $this->seniorEditorWithDomainScope('ahlulbayt');
        $this->approveAndPublishEntity($editor, $senior, $uuid);

        $entityId = DB::table('entities')->where('uuid', $uuid)->value('id');

        $this->assertDatabaseHas('seo_metadata', ['entity_id' => $entityId]);

        $structured = DB::table('seo_metadata')
            ->where('entity_id', $entityId)
            ->value('structured_data');

        $this->assertNotNull($structured);
        $decoded = json_decode((string) $structured, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('@context', $decoded);
        $this->assertArrayHasKey('@type', $decoded);
    }

    private function seedSitemapEntry(): void
    {
        $languageId = (int) DB::table('languages')->where('code', 'ar')->value('id');
        $typeId = (int) DB::table('entity_types')->where('code', 'person')->value('id');
        $uuid = Uuid::uuid7()->toString();

        $entityId = (int) DB::table('entities')->insertGetId([
            'uuid' => $uuid,
            'entity_type_id' => $typeId,
            'workflow_state' => 'published',
            'module_flags' => json_encode(['general' => true]),
            'is_indexable' => true,
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        SitemapEntry::query()->create([
            'entity_id' => $entityId,
            'language_id' => $languageId,
            'loc_path' => '/ar/persons/'.$uuid,
            'lastmod' => now(),
            'changefreq' => 'weekly',
            'priority' => 0.8,
            'is_active' => true,
        ]);
    }
}
