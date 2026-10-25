<?php

declare(strict_types=1);

namespace Tests\Feature\Analytics;

use App\Modules\Analytics\Jobs\RecordAnalyticsEventJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Tests\Support\EditorialTestHelpers;
use Tests\TestCase;

final class AnalyticsApiTest extends TestCase
{
    use EditorialTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedKnowledgeBase();
        $this->setUpEditorialHttp();
    }

    public function test_record_view_returns_202_and_dispatches_job(): void
    {
        Bus::fake();

        $response = $this->postJson('/api/v1/analytics/views', [
            'locale' => 'ar',
            'path' => '/ar/persons/test',
            'session_id' => 'session-abc',
        ]);

        $response->assertAccepted()
            ->assertJsonPath('data.accepted', true);

        Bus::assertDispatched(RecordAnalyticsEventJob::class);
    }

    public function test_popular_entities_api(): void
    {
        $entityId = $this->seedPublishedEntity();

        DB::table('analytics_events')->insert([
            'event_type' => 'page_view',
            'entity_id' => $entityId,
            'session_hash' => hash('sha256', 's1'),
            'occurred_at' => now(),
        ]);

        DB::table('analytics_events')->insert([
            'event_type' => 'page_view',
            'entity_id' => $entityId,
            'session_hash' => hash('sha256', 's2'),
            'occurred_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/ar/analytics/popular?days=7&limit=5');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.view_count', 2);
    }

    public function test_admin_analytics_overview(): void
    {
        [$admin] = $this->verifiedUserToken('admin');

        DB::table('analytics_events')->insert([
            'event_type' => 'page_view',
            'entity_id' => $this->seedPublishedEntity(),
            'session_hash' => hash('sha256', 's1'),
            'occurred_at' => now(),
        ]);

        $response = $this->getJsonAs($admin, '/api/v1/admin/analytics/overview?days=30');

        $response->assertOk()
            ->assertJsonPath('data.total_page_views', 1);
    }

    private function seedPublishedEntity(): int
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
            'title' => 'شخصية',
            'slug' => 'test-person',
            'summary' => 'ملخص',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $entityId;
    }
}
