<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Services;

use App\Modules\KnowledgeCore\Domain\ValueObjects\EntityType;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityType as EntityTypeModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class EntityTypeRegistry
{
    public function findByCode(string $code): ?EntityType
    {
        $model = EntityTypeModel::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        return $model !== null ? $this->toValueObject($model) : null;
    }

    public function getByCode(string $code): EntityType
    {
        $type = $this->findByCode($code);

        if ($type === null) {
            throw (new ModelNotFoundException)->setModel(EntityTypeModel::class, [$code]);
        }

        return $type;
    }

    public function resolveId(string $code): int
    {
        return $this->getByCode($code)->id();
    }

    private function toValueObject(EntityTypeModel $model): EntityType
    {
        return new EntityType(
            id: $model->id,
            code: $model->code,
            class: $model->class,
            profileTable: $model->profile_table,
            primaryDomain: $model->primary_domain,
            isMvp: (bool) $model->is_mvp,
            isActive: (bool) $model->is_active,
        );
    }
}
