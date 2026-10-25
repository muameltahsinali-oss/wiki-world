<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Services;

use App\Modules\KnowledgeCore\Domain\Exceptions\SlugConflictException;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityLocalization;
use Illuminate\Support\Str;

final class EntitySlugValidator
{
    public function resolveSlug(
        string $title,
        ?string $slug,
        int $languageId,
        ?int $excludeLocalizationId = null,
    ): string {
        $candidate = Str::slug($slug !== null && $slug !== '' ? $slug : $title);

        if ($candidate === '') {
            $candidate = 'entity-'.Str::lower(Str::random(8));
        }

        $query = EntityLocalization::query()
            ->where('language_id', $languageId)
            ->where('slug', $candidate);

        if ($excludeLocalizationId !== null) {
            $query->where('id', '!=', $excludeLocalizationId);
        }

        if ($query->exists()) {
            throw new SlugConflictException;
        }

        return $candidate;
    }
}
