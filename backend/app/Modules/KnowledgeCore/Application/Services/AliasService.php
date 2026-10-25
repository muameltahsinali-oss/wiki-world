<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Services;

use App\Modules\KnowledgeCore\Domain\Support\EntityUuidGenerator;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityAlias;
use App\Modules\Users\Infrastructure\Models\Language;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class AliasService
{
    public function __construct(
        private readonly EntityUuidGenerator $uuidGenerator,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function list(int $entityId): array
    {
        return EntityAlias::query()
            ->where('entity_id', $entityId)
            ->orderBy('text')
            ->get()
            ->map(static function (EntityAlias $alias): array {
                $locale = null;

                if ($alias->language_id !== null) {
                    $locale = Language::query()->where('id', $alias->language_id)->value('code');
                }

                return [
                    'uuid' => $alias->uuid,
                    'text' => $alias->text,
                    'alias_type' => $alias->alias_type,
                    'locale' => $locale,
                    'created_at' => $alias->created_at?->toIso8601String(),
                ];
            })
            ->all();
    }

    public function add(
        int $entityId,
        string $text,
        string $aliasType,
        ?string $locale = null,
    ): EntityAlias {
        $languageId = null;

        if ($locale !== null) {
            $languageId = Language::query()->where('code', $locale)->value('id');

            if ($languageId === null) {
                throw new \InvalidArgumentException('Unsupported locale.');
            }
        }

        return EntityAlias::query()->create([
            'uuid' => $this->uuidGenerator->generateString(),
            'entity_id' => $entityId,
            'language_id' => $languageId,
            'text' => $text,
            'alias_type' => $aliasType,
            'created_at' => now(),
        ]);
    }

    public function delete(string $aliasUuid): void
    {
        $alias = EntityAlias::query()->where('uuid', $aliasUuid)->first();

        if ($alias === null) {
            throw (new ModelNotFoundException)->setModel(EntityAlias::class, [$aliasUuid]);
        }

        $alias->delete();
    }
}
