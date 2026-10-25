<?php

declare(strict_types=1);

namespace Tests\Feature\KnowledgeCore;

use App\Modules\KnowledgeCore\Application\Services\EntityCacheService;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Tests\Support\EditorialTestHelpers;
use Tests\TestCase;

final class EntityCacheTest extends TestCase
{
    use EditorialTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedKnowledgeBase();
        $this->setUpEditorialHttp();
    }

    public function test_public_entity_uses_cache_on_second_request(): void
    {
        $uuid = $this->seedPublishedEntityUuid();
        $cache = $this->app->make(EntityCacheService::class);
        $cache->forget($uuid, 'ar');

        $this->getJson('/api/v1/ar/entities/'.$uuid)->assertOk();
        $this->assertNotNull($cache->get($uuid, 'ar'));

        $this->getJson('/api/v1/ar/entities/'.$uuid)
            ->assertOk()
            ->assertJsonPath('data.uuid', $uuid);
    }

    private function seedPublishedEntityUuid(): string
    {
        $typeId = (int) DB::table('entity_types')->where('code', 'person')->value('id');
        $languageId = (int) DB::table('languages')->where('code', 'ar')->value('id');
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

        DB::table('entity_localizations')->insert([
            'entity_id' => $entityId,
            'language_id' => $languageId,
            'title' => 'Cached Entity',
            'slug' => 'cached-entity',
            'summary' => 'Summary',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $uuid;
    }
}
