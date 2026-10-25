<?php

declare(strict_types=1);

namespace App\Modules\SEO\Application\Services;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityLocalization;

final class StructuredDataService
{
    /**
     * @return array<string, mixed>
     */
    public function build(Entity $entity, EntityLocalization $localization, string $locale): array
    {
        $typeCode = $entity->entityType?->code ?? 'article';
        $schemaType = $this->schemaType($typeCode);
        $baseUrl = rtrim((string) config('app.url'), '/');
        $path = sprintf('/%s/%ss/%s', $locale, $typeCode, $localization->slug ?: $entity->uuid);
        $url = $baseUrl.$path;

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => $schemaType,
            'name' => $localization->title,
            'description' => $localization->summary ?? $localization->meta_description,
            'url' => $url,
            'inLanguage' => $locale,
        ];

        if ($schemaType === 'Person') {
            $jsonLd['@type'] = 'Person';
        }

        if ($schemaType === 'Event') {
            $jsonLd['eventStatus'] = 'https://schema.org/EventScheduled';
        }

        if ($schemaType === 'Book') {
            $jsonLd['bookFormat'] = 'https://schema.org/EBook';
        }

        return $jsonLd;
    }

    private function schemaType(string $entityTypeCode): string
    {
        return match ($entityTypeCode) {
            'person' => 'Person',
            'article' => 'Article',
            'event' => 'Event',
            'place' => 'Place',
            'book' => 'Book',
            'concept', 'source', 'verse' => 'CreativeWork',
            default => 'CreativeWork',
        };
    }
}
