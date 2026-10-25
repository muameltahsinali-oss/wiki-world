<?php

declare(strict_types=1);

namespace Tests\Feature\KnowledgeCore;

use App\Models\User;
use App\Modules\RolesPermissions\Application\Services\RoleResolver;
use App\Modules\RolesPermissions\Domain\Enums\Role;
use App\Modules\Users\Application\Contracts\UserRepositoryInterface;
use App\Modules\Users\Application\DTOs\CreateUserData;
use Database\Seeders\EntityTypeSeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CreateEntityDraftEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            LanguageSeeder::class,
            RolePermissionSeeder::class,
            EntityTypeSeeder::class,
        ]);
    }

    public function test_editor_with_domain_scope_creates_draft_entity(): void
    {
        [, $token] = $this->editorWithDomainScope('ahlulbayt');

        $response = $this->withToken($token)->postJson('/api/v1/editorial/entities', [
            'entity_type' => 'person',
            'domain' => 'ahlulbayt',
            'locale' => 'ar',
            'title' => 'الإمام علي بن أبي طالب',
            'slug' => 'imam-ali',
            'summary' => 'أول الأئمة',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.entity_type', 'person')
            ->assertJsonPath('data.domain', 'ahlulbayt')
            ->assertJsonPath('data.workflow_state', 'draft')
            ->assertJsonPath('data.locale', 'ar')
            ->assertJsonPath('data.slug', 'imam-ali')
            ->assertJsonStructure(['data' => ['uuid', 'title', 'created_at']]);

        $uuid = $response->json('data.uuid');

        $this->assertDatabaseHas('entities', [
            'uuid' => $uuid,
            'workflow_state' => 'draft',
        ]);

        $this->assertDatabaseHas('entity_localizations', [
            'title' => 'الإمام علي بن أبي طالب',
            'slug' => 'imam-ali',
        ]);
    }

    public function test_editor_without_domain_scope_is_forbidden(): void
    {
        [, $token] = $this->editorWithDomainScope('general');

        $this->withToken($token)->postJson('/api/v1/editorial/entities', [
            'entity_type' => 'person',
            'domain' => 'ahlulbayt',
            'locale' => 'ar',
            'title' => 'Test Person',
        ])
            ->assertForbidden()
            ->assertJsonPath('error.code', 'DOMAIN_SCOPE_DENIED');
    }

    public function test_registered_user_cannot_create_entities(): void
    {
        [, $token] = $this->verifiedUserToken(Role::Registered->value);

        $this->withToken($token)->postJson('/api/v1/editorial/entities', [
            'entity_type' => 'person',
            'domain' => 'general',
            'locale' => 'ar',
            'title' => 'Test',
        ])
            ->assertForbidden();
    }

    public function test_verse_entity_type_is_rejected(): void
    {
        [, $token] = $this->editorWithDomainScope('quran');

        $this->withToken($token)->postJson('/api/v1/editorial/entities', [
            'entity_type' => 'verse',
            'domain' => 'quran',
            'locale' => 'ar',
            'title' => 'آية',
        ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'NON_CREATABLE_ENTITY_TYPE');
    }

    public function test_duplicate_slug_returns_conflict(): void
    {
        [, $token] = $this->editorWithDomainScope('general');

        $payload = [
            'entity_type' => 'concept',
            'domain' => 'general',
            'locale' => 'ar',
            'title' => 'مفهوم',
            'slug' => 'shared-slug',
        ];

        $this->withToken($token)->postJson('/api/v1/editorial/entities', $payload)->assertCreated();

        $this->withToken($token)->postJson('/api/v1/editorial/entities', [
            ...$payload,
            'title' => 'مفهوم آخر',
        ])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'SLUG_CONFLICT');
    }

    /**
     * @return array{0: User, 1: string}
     */
    private function editorWithDomainScope(string $domain): array
    {
        [$user, $token] = $this->verifiedUserToken(Role::Editor->value);

        DB::table('editor_domain_scopes')->insert([
            'user_id' => $user->id,
            'domain_code' => $domain,
        ]);

        return [$user, $token];
    }

    /**
     * @return array{0: User, 1: string}
     */
    private function verifiedUserToken(string $role): array
    {
        $repository = $this->app->make(UserRepositoryInterface::class);
        $user = $repository->create(new CreateUserData(
            email: $role.'-'.uniqid().'@wikiworld.test',
            password: 'SecurePass123!',
        ));
        $repository->assignRole($user->uuid, $role);

        $model = User::query()->where('uuid', $user->uuid)->firstOrFail();
        $model->markEmailAsVerified();

        $abilities = $this->app->make(RoleResolver::class)->tokenAbilitiesForUser($model);
        $token = $model->createToken('test', $abilities)->plainTextToken;

        return [$model, $token];
    }
}
