<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Domain\Entities;

use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\KnowledgeCore\Domain\ValueObjects\EntityType;

final readonly class KnowledgeEntity
{
    /**
     * @param  array<string, mixed>  $moduleFlags
     */
    public function __construct(
        private int $id,
        public string $uuid,
        public EntityType $entityType,
        public WorkflowState $workflowState,
        public array $moduleFlags,
        public bool $isIndexable,
        public ?string $publishedAt,
        public ?int $publishedVersionId,
        public ?int $createdByUserId,
        public ?int $updatedByUserId,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function withWorkflowState(WorkflowState $workflowState): self
    {
        return new self(
            id: $this->id,
            uuid: $this->uuid,
            entityType: $this->entityType,
            workflowState: $workflowState,
            moduleFlags: $this->moduleFlags,
            isIndexable: $this->isIndexable,
            publishedAt: $this->publishedAt,
            publishedVersionId: $this->publishedVersionId,
            createdByUserId: $this->createdByUserId,
            updatedByUserId: $this->updatedByUserId,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    public function withUpdatedByUserId(?int $updatedByUserId): self
    {
        return new self(
            id: $this->id,
            uuid: $this->uuid,
            entityType: $this->entityType,
            workflowState: $this->workflowState,
            moduleFlags: $this->moduleFlags,
            isIndexable: $this->isIndexable,
            publishedAt: $this->publishedAt,
            publishedVersionId: $this->publishedVersionId,
            createdByUserId: $this->createdByUserId,
            updatedByUserId: $updatedByUserId,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    public function withPublishedAt(?string $publishedAt): self
    {
        return new self(
            id: $this->id,
            uuid: $this->uuid,
            entityType: $this->entityType,
            workflowState: $this->workflowState,
            moduleFlags: $this->moduleFlags,
            isIndexable: $this->isIndexable,
            publishedAt: $publishedAt,
            publishedVersionId: $this->publishedVersionId,
            createdByUserId: $this->createdByUserId,
            updatedByUserId: $this->updatedByUserId,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    public function withPublishedVersionId(?int $publishedVersionId): self
    {
        return new self(
            id: $this->id,
            uuid: $this->uuid,
            entityType: $this->entityType,
            workflowState: $this->workflowState,
            moduleFlags: $this->moduleFlags,
            isIndexable: $this->isIndexable,
            publishedAt: $this->publishedAt,
            publishedVersionId: $publishedVersionId,
            createdByUserId: $this->createdByUserId,
            updatedByUserId: $this->updatedByUserId,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }
}
