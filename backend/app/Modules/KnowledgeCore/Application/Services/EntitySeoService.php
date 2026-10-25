<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Services;

use App\Modules\KnowledgeCore\Infrastructure\Models\EntityLocalization;
use App\Modules\Users\Infrastructure\Models\Language;

final class EntitySeoService
{
    /**
     * @return array{locale: string, meta_title: ?string, meta_description: ?string}
     */
    public function get(int $entityId, string $locale): array
    {
        $localization = $this->localization($entityId, $locale);

        return [
            'locale' => $locale,
            'meta_title' => $localization?->meta_title,
            'meta_description' => $localization?->meta_description,
        ];
    }

    /**
     * @param  array{meta_title?: ?string, meta_description?: ?string}  $data
     * @return array{locale: string, meta_title: ?string, meta_description: ?string}
     */
    public function update(int $entityId, string $locale, array $data): array
    {
        $localization = $this->localization($entityId, $locale);

        if ($localization === null) {
            throw new \InvalidArgumentException('Localization not found for SEO update.');
        }

        if (array_key_exists('meta_title', $data)) {
            $localization->meta_title = $data['meta_title'];
        }

        if (array_key_exists('meta_description', $data)) {
            $localization->meta_description = $data['meta_description'];
        }

        $localization->save();

        return $this->get($entityId, $locale);
    }

    private function localization(int $entityId, string $locale): ?EntityLocalization
    {
        $languageId = Language::query()->where('code', $locale)->value('id');

        if ($languageId === null) {
            throw new \InvalidArgumentException('Unsupported locale.');
        }

        return EntityLocalization::query()
            ->where('entity_id', $entityId)
            ->where('language_id', $languageId)
            ->first();
    }
}
