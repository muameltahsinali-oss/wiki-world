<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\DB;
use Tests\Support\EditorialTestHelpers;
use Tests\TestCase;

final class AdminApiTest extends TestCase
{
    use EditorialTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedKnowledgeBase();
        $this->setUpEditorialHttp();
    }

    public function test_admin_users_list_and_role_assignment(): void
    {
        [$admin] = $this->verifiedUserToken('admin');
        [$target] = $this->verifiedUserToken('registered');

        $this->getJsonAs($admin, '/api/v1/admin/users')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['pagination']]);

        $this->postJsonAs($admin, '/api/v1/admin/users/'.$target->uuid.'/roles', [
            'role' => 'editor',
        ])->assertOk()
            ->assertJsonFragment(['editor']);

        $this->deleteJsonAs($admin, '/api/v1/admin/users/'.$target->uuid.'/roles/editor')
            ->assertOk();
    }

    public function test_admin_settings_and_feature_toggles(): void
    {
        [$admin] = $this->verifiedUserToken('admin');

        $this->getJsonAs($admin, '/api/v1/admin/settings')
            ->assertOk()
            ->assertJsonStructure(['data' => ['maintenance_mode', 'feature_toggles']]);

        $this->patchJsonAs($admin, '/api/v1/admin/settings', [
            'maintenance_mode' => true,
        ])->assertOk()
            ->assertJsonPath('data.maintenance_mode', true);

        $this->patchJsonAs($admin, '/api/v1/admin/feature-toggles', [
            'toggles' => ['ai_enabled' => false],
        ])->assertOk();
    }

    public function test_admin_audit_logs(): void
    {
        [$admin] = $this->verifiedUserToken('admin');

        DB::table('audit_logs')->insert([
            'actor_user_id' => $admin->id,
            'action' => 'user.create',
            'subject_type' => 'user',
            'subject_id' => $admin->id,
            'diff' => json_encode(['email' => 'test@example.com']),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'created_at' => now(),
        ]);

        $this->getJsonAs($admin, '/api/v1/admin/audit-logs?filter[action]=user.create')
            ->assertOk()
            ->assertJsonFragment(['action' => 'user.create']);
    }

    public function test_admin_domain_scopes_update(): void
    {
        [$admin] = $this->verifiedUserToken('admin');
        [$target] = $this->verifiedUserToken('editor');

        $response = $this->putJsonAs($admin, '/api/v1/admin/users/'.$target->uuid.'/domain-scopes', [
            'domain_scopes' => ['quran', 'ahlulbayt'],
        ]);

        $response->assertOk();
        $scopes = $response->json('data.domain_scopes');
        sort($scopes);
        $this->assertSame(['ahlulbayt', 'quran'], $scopes);
    }
}
