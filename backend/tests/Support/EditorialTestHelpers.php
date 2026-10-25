<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\User;
use App\Modules\RolesPermissions\Application\Services\RoleResolver;
use App\Modules\Users\Application\Contracts\UserRepositoryInterface;
use App\Modules\Users\Application\DTOs\CreateUserData;
use Database\Seeders\EntityTypeSeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\RelationshipTypeSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Laravel\Sanctum\Sanctum;

trait EditorialTestHelpers
{
    use RefreshDatabase;

    protected function setUpEditorialHttp(): void
    {
        $this->withoutMiddleware(EnsureFrontendRequestsAreStateful::class);
    }

    protected function seedKnowledgeBase(): void
    {
        $this->seed([
            LanguageSeeder::class,
            RolePermissionSeeder::class,
            EntityTypeSeeder::class,
            RelationshipTypeSeeder::class,
        ]);
    }

    /**
     * @return array{0: User, 1: string}
     */
    protected function editorWithDomainScope(string $domain, string $role = 'editor'): array
    {
        [$user, $token] = $this->verifiedUserToken($role);

        DB::table('editor_domain_scopes')->insert([
            'user_id' => $user->id,
            'domain_code' => $domain,
        ]);

        return [$user, $token];
    }

    /**
     * @return array{0: User, 1: string}
     */
    protected function verifiedUserToken(string $role): array
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

    protected function createDraftEntity(string $token, array $overrides = []): string
    {
        $response = $this->withToken($token)->postJson('/api/v1/editorial/entities', array_merge([
            'entity_type' => 'person',
            'domain' => 'ahlulbayt',
            'locale' => 'ar',
            'title' => 'كيان تجريبي',
            'slug' => 'test-entity-'.uniqid(),
        ], $overrides));

        $response->assertCreated();

        return (string) $response->json('data.uuid');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function saveEntityDraft(string $token, string $uuid, array $payload = []): TestResponse
    {
        return $this->withToken($token)->patchJson('/api/v1/editorial/entities/'.$uuid, array_merge([
            'locale' => 'ar',
        ], $payload));
    }

    protected function seniorEditorWithDomainScope(string $domain): array
    {
        return $this->editorWithDomainScope($domain, 'senior_editor');
    }

    protected function addEntityReference(string $entityUuid): void
    {
        $entityId = (int) DB::table('entities')->where('uuid', $entityUuid)->value('id');

        DB::table('references')->insert([
            'source_entity_id' => $entityId,
            'referencer_type' => 'entity',
            'referencer_id' => $entityId,
            'entity_id' => $entityId,
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function postJsonAs(User $user, string $uri, array $data = []): TestResponse
    {
        $abilities = $this->app->make(RoleResolver::class)->tokenAbilitiesForUser($user);
        Sanctum::actingAs($user, $abilities, 'sanctum');

        return $this->postJson($uri, $data);
    }

    protected function getJsonAs(User $user, string $uri): TestResponse
    {
        $abilities = $this->app->make(RoleResolver::class)->tokenAbilitiesForUser($user);
        Sanctum::actingAs($user, $abilities, 'sanctum');

        return $this->getJson($uri);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function patchJsonAs(User $user, string $uri, array $data = []): TestResponse
    {
        $abilities = $this->app->make(RoleResolver::class)->tokenAbilitiesForUser($user);
        Sanctum::actingAs($user, $abilities, 'sanctum');

        return $this->patchJson($uri, $data);
    }

    protected function deleteJsonAs(User $user, string $uri): TestResponse
    {
        $abilities = $this->app->make(RoleResolver::class)->tokenAbilitiesForUser($user);
        Sanctum::actingAs($user, $abilities, 'sanctum');

        return $this->deleteJson($uri);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function putJsonAs(User $user, string $uri, array $data = []): TestResponse
    {
        $abilities = $this->app->make(RoleResolver::class)->tokenAbilitiesForUser($user);
        Sanctum::actingAs($user, $abilities, 'sanctum');

        return $this->putJson($uri, $data);
    }

    /**
     * @return array{0: User, 1: string}
     */
    protected function reviewerToken(): array
    {
        return $this->verifiedUserToken('reviewer');
    }

    protected function approveAndPublishEntity(User $editor, User $senior, string $uuid, string $locale = 'ar'): void
    {
        $this->addEntityReference($uuid);

        $submit = $this->postJsonAs($editor, '/api/v1/editorial/entities/'.$uuid.'/submit');
        $submit->assertOk();
        $submissionId = (int) $submit->json('data.submission_id');

        $this->postJsonAs($senior, '/api/v1/editorial/submissions/'.$submissionId.'/review', [
            'decision' => 'approve',
        ])->assertOk();

        $this->postJsonAs($senior, '/api/v1/editorial/entities/'.$uuid.'/publish', [
            'locale' => $locale,
        ])->assertOk();
    }
}
