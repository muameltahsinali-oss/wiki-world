<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Modules\Shared\Application\Services\FrontendRevalidationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Support\EditorialTestHelpers;
use Tests\TestCase;

final class FrontendRevalidationServiceTest extends TestCase
{
    use EditorialTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedKnowledgeBase();
    }

    public function test_paths_for_entity_includes_all_locales(): void
    {
        $languageId = (int) DB::table('languages')->where('code', 'ar')->value('id');
        $typeId = (int) DB::table('entity_types')->where('code', 'person')->value('id');
        $uuid = (string) Str::uuid();

        $entityId = (int) DB::table('entities')->insertGetId([
            'uuid' => $uuid,
            'entity_type_id' => $typeId,
            'workflow_state' => 'published',
            'module_flags' => '{}',
            'is_indexable' => true,
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('entity_localizations')->insert([
            'entity_id' => $entityId,
            'language_id' => $languageId,
            'title' => 'Test Person',
            'slug' => 'test-person',
            'workflow_state' => 'published',
            'translation_status' => 'original',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = $this->app->make(FrontendRevalidationService::class);
        $paths = $service->pathsForEntity($entityId);

        $this->assertContains('/ar/persons/'.$uuid, $paths);
        $this->assertContains('/en/persons/'.$uuid, $paths);
    }

    public function test_revalidate_posts_paths_to_frontend(): void
    {
        config([
            'wikiworld.frontend.url' => 'http://frontend.test',
            'wikiworld.frontend.revalidate_secret' => 'test-secret',
        ]);

        Http::fake([
            'frontend.test/api/revalidate*' => Http::response(['revalidated' => true], 200),
        ]);

        $service = $this->app->make(FrontendRevalidationService::class);
        $service->revalidate(['/ar/persons/abc', '/en/persons/abc']);

        Http::assertSent(function ($request): bool {
            return str_contains($request->url(), 'secret=test-secret')
                && $request['paths'] === ['/ar/persons/abc', '/en/persons/abc'];
        });
    }
}
