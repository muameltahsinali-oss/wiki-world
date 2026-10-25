<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Contracts;

use App\Modules\KnowledgeCore\Application\DTOs\CreateKnowledgeEntityData;
use App\Modules\KnowledgeCore\Domain\Entities\KnowledgeEntity;

interface KnowledgeEntityRepositoryInterface
{
    public function findByUuid(string $uuid): ?KnowledgeEntity;

    public function create(CreateKnowledgeEntityData $data): KnowledgeEntity;

    public function save(KnowledgeEntity $entity): KnowledgeEntity;

    public function delete(string $uuid): void;
}
