<?php

declare(strict_types=1);

namespace Tests\Feature\KnowledgeCore;

use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\DTOs\CreateKnowledgeEntityData;
use App\Modules\KnowledgeCore\Domain\Entities\KnowledgeEntity;
use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use Database\Seeders\EntityTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class KnowledgeEntityRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private KnowledgeEntityRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EntityTypeSeeder::class);
        $this->repository = $this->app->make(KnowledgeEntityRepositoryInterface::class);
    }

    public function test_create_persists_entity_with_uuid_v7_and_draft_state(): void
    {
        $entity = $this->repository->create(new CreateKnowledgeEntityData(
            entityTypeCode: 'person',
            moduleFlags: ['featured' => true],
        ));

        $this->assertTrue(Uuid::isValid($entity->uuid));
        $this->assertSame(7, Uuid::fromString($entity->uuid)->getVersion());
        $this->assertSame(WorkflowState::Draft, $entity->workflowState);
        $this->assertSame('person', $entity->entityType->code);
        $this->assertDatabaseHas('entities', [
            'uuid' => $entity->uuid,
            'workflow_state' => 'draft',
        ]);
    }

    public function test_find_by_uuid_returns_entity(): void
    {
        $created = $this->repository->create(new CreateKnowledgeEntityData(
            entityTypeCode: 'event',
        ));

        $found = $this->repository->findByUuid($created->uuid);

        $this->assertNotNull($found);
        $this->assertSame($created->uuid, $found->uuid);
        $this->assertSame('event', $found->entityType->code);
    }

    public function test_save_updates_mutable_fields_only(): void
    {
        $created = $this->repository->create(new CreateKnowledgeEntityData(
            entityTypeCode: 'concept',
        ));

        $updated = $this->repository->save(new KnowledgeEntity(
            id: $created->id(),
            uuid: $created->uuid,
            entityType: $created->entityType,
            workflowState: WorkflowState::Submitted,
            moduleFlags: ['review' => true],
            isIndexable: false,
            publishedAt: null,
            publishedVersionId: null,
            createdByUserId: null,
            updatedByUserId: null,
            createdAt: $created->createdAt,
            updatedAt: $created->updatedAt,
        ));

        $this->assertSame(WorkflowState::Submitted, $updated->workflowState);
        $this->assertFalse($updated->isIndexable);
        $this->assertSame('concept', $updated->entityType->code);
    }

    public function test_delete_soft_deletes_entity(): void
    {
        $created = $this->repository->create(new CreateKnowledgeEntityData(
            entityTypeCode: 'place',
        ));

        $this->repository->delete($created->uuid);

        $this->assertSoftDeleted('entities', ['uuid' => $created->uuid]);
        $this->assertNull($this->repository->findByUuid($created->uuid));
    }
}
