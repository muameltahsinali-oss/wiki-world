<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Modules\Users\Application\Contracts\UserRepositoryInterface;
use App\Modules\Users\Application\DTOs\CreateUserData;
use App\Modules\Users\Application\DTOs\UpdateUserData;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(LanguageSeeder::class);
        $this->repository = $this->app->make(UserRepositoryInterface::class);
    }

    public function test_create_persists_user_with_uuid_v7(): void
    {
        $user = $this->repository->create(new CreateUserData(
            email: 'learner@wikiworld.test',
            password: 'secure-password-12',
            displayName: 'Learner',
            preferredLocale: 'ar',
        ));

        $this->assertTrue(Uuid::isValid($user->uuid));
        $this->assertSame(7, Uuid::fromString($user->uuid)->getVersion());
        $this->assertDatabaseHas('users', [
            'email' => 'learner@wikiworld.test',
            'display_name' => 'Learner',
        ]);
    }

    public function test_find_by_uuid_and_email(): void
    {
        $created = $this->repository->create(new CreateUserData(
            email: 'editor@wikiworld.test',
            password: 'secure-password-12',
        ));

        $byUuid = $this->repository->findByUuid($created->uuid);
        $byEmail = $this->repository->findByEmail('editor@wikiworld.test');

        $this->assertNotNull($byUuid);
        $this->assertNotNull($byEmail);
        $this->assertSame($created->uuid, $byUuid->uuid);
        $this->assertSame($created->uuid, $byEmail->uuid);
    }

    public function test_update_user_fields(): void
    {
        $created = $this->repository->create(new CreateUserData(
            email: 'patch@wikiworld.test',
            password: 'secure-password-12',
            preferredLocale: 'ar',
        ));

        $updated = $this->repository->update($created->uuid, new UpdateUserData(
            displayName: 'Updated Name',
            preferredLocale: 'en',
        ));

        $this->assertSame('Updated Name', $updated->displayName);
        $this->assertSame('en', $updated->preferredLocale);
    }

    public function test_delete_soft_deletes_user(): void
    {
        $created = $this->repository->create(new CreateUserData(
            email: 'delete@wikiworld.test',
            password: 'secure-password-12',
        ));

        $this->repository->delete($created->uuid);

        $this->assertSoftDeleted('users', ['uuid' => $created->uuid]);
        $this->assertNull($this->repository->findByUuid($created->uuid));
    }

    public function test_user_data_exposes_uuid_not_internal_id(): void
    {
        $created = $this->repository->create(new CreateUserData(
            email: 'api@wikiworld.test',
            password: 'secure-password-12',
        ));

        $data = $this->repository->toData($created);
        $array = $data->toArray();

        $this->assertArrayHasKey('uuid', $array);
        $this->assertArrayNotHasKey('id', $array);
        $this->assertSame($created->uuid, $array['uuid']);
    }
}
