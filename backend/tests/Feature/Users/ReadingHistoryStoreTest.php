<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use Database\Seeders\AhlulbaytLaunchSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Support\EditorialTestHelpers;
use Tests\TestCase;

final class ReadingHistoryStoreTest extends TestCase
{
    use EditorialTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedKnowledgeBase();
        $this->seed(AhlulbaytLaunchSeeder::class);
        $this->setUpEditorialHttp();
    }

    public function test_user_can_record_reading_history(): void
    {
        [$user] = $this->verifiedUserToken('registered');
        $entityUuid = (string) DB::table('entities')->value('uuid');

        $this->postJsonAs($user, '/api/v1/me/history', ['entity_uuid' => $entityUuid])
            ->assertCreated()
            ->assertJsonPath('data.entity_uuid', $entityUuid);

        $this->assertDatabaseHas('reading_history', [
            'user_id' => $user->id,
        ]);
    }

    public function test_duplicate_history_entry_updates_viewed_at(): void
    {
        [$user] = $this->verifiedUserToken('registered');
        $entityUuid = (string) DB::table('entities')->value('uuid');

        $this->postJsonAs($user, '/api/v1/me/history', ['entity_uuid' => $entityUuid])->assertCreated();
        $firstViewedAt = DB::table('reading_history')->where('user_id', $user->id)->value('viewed_at');

        $this->travel(1)->hours();

        $this->postJsonAs($user, '/api/v1/me/history', ['entity_uuid' => $entityUuid])->assertCreated();
        $secondViewedAt = DB::table('reading_history')->where('user_id', $user->id)->value('viewed_at');

        $this->assertNotSame($firstViewedAt, $secondViewedAt);
        $this->assertSame(1, DB::table('reading_history')->where('user_id', $user->id)->count());
    }
}
