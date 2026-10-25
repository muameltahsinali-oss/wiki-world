<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Services;

use App\Modules\KnowledgeCore\Infrastructure\Models\BookProfile;
use App\Modules\KnowledgeCore\Infrastructure\Models\ConceptProfile;
use App\Modules\KnowledgeCore\Infrastructure\Models\EventProfile;
use App\Modules\KnowledgeCore\Infrastructure\Models\PersonProfile;
use App\Modules\KnowledgeCore\Infrastructure\Models\PlaceProfile;
use App\Modules\KnowledgeCore\Infrastructure\Models\SourceProfile;
use Illuminate\Database\Eloquent\Model;

final class ProfileService
{
    /**
     * @var array<string, class-string<Model>>
     */
    private const PROFILE_MODELS = [
        'person' => PersonProfile::class,
        'event' => EventProfile::class,
        'place' => PlaceProfile::class,
        'concept' => ConceptProfile::class,
        'source' => SourceProfile::class,
        'book' => BookProfile::class,
    ];

    /**
     * @param  array<string, mixed>  $profileData
     */
    public function upsertProfile(string $entityTypeCode, int $entityId, array $profileData): Model
    {
        $modelClass = self::PROFILE_MODELS[$entityTypeCode] ?? null;

        if ($modelClass === null) {
            throw new \InvalidArgumentException("Unsupported profile type: {$entityTypeCode}.");
        }

        /** @var Model $model */
        $model = $modelClass::query()->updateOrCreate(
            ['entity_id' => $entityId],
            [...$profileData, 'entity_id' => $entityId],
        );

        return $model;
    }
}
