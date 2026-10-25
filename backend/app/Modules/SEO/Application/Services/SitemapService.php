<?php

declare(strict_types=1);

namespace App\Modules\SEO\Application\Services;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityLocalization;
use App\Modules\SEO\Infrastructure\Models\SeoMetadata;
use App\Modules\SEO\Infrastructure\Models\SitemapEntry;
use App\Modules\Users\Infrastructure\Models\Language;

final class SitemapService
{
    public function __construct(
        private readonly StructuredDataService $structuredData,
    ) {}

    public function upsertForEntity(int $entityId, ?string $locale = null): void
    {
        $entity = Entity::query()->with('entityType')->find($entityId);

        if ($entity === null || $entity->workflow_state !== 'published') {
            SitemapEntry::query()->where('entity_id', $entityId)->update(['is_active' => false]);

            return;
        }

        $localizations = EntityLocalization::query()
            ->where('entity_id', $entityId)
            ->when($locale !== null, function ($q) use ($locale): void {
                $languageId = Language::query()->where('code', $locale)->value('id');
                if ($languageId !== null) {
                    $q->where('language_id', $languageId);
                }
            })
            ->get();

        foreach ($localizations as $localization) {
            $language = Language::query()->find($localization->language_id);
            if ($language === null) {
                continue;
            }

            $typeCode = $entity->entityType?->code ?? 'article';
            $locPath = sprintf('/%s/%ss/%s', $language->code, $typeCode, $localization->slug ?: $entity->uuid);

            SitemapEntry::query()->updateOrCreate(
                [
                    'entity_id' => $entity->id,
                    'language_id' => $localization->language_id,
                ],
                [
                    'loc_path' => $locPath,
                    'lastmod' => now(),
                    'changefreq' => 'weekly',
                    'priority' => 0.7,
                    'is_active' => true,
                ],
            );
        }
    }

    public function upsertSeoMetadata(int $entityId, ?string $locale = null): void
    {
        $entity = Entity::query()->with('entityType')->find($entityId);

        if ($entity === null || $entity->workflow_state !== 'published') {
            return;
        }

        $localizations = EntityLocalization::query()
            ->where('entity_id', $entityId)
            ->when($locale !== null, function ($q) use ($locale): void {
                $languageId = Language::query()->where('code', $locale)->value('id');
                if ($languageId !== null) {
                    $q->where('language_id', $languageId);
                }
            })
            ->get();

        foreach ($localizations as $localization) {
            $language = Language::query()->find($localization->language_id);
            if ($language === null) {
                continue;
            }

            $jsonLd = $this->structuredData->build($entity, $localization, $language->code);

            SeoMetadata::query()->updateOrCreate(
                [
                    'entity_id' => $entity->id,
                    'language_id' => $localization->language_id,
                ],
                [
                    'meta_title' => $localization->meta_title ?: $localization->title,
                    'meta_description' => $localization->meta_description ?: $localization->summary,
                    'structured_data' => $jsonLd,
                    'updated_at' => now(),
                ],
            );
        }
    }

    public function renderIndexXml(): string
    {
        $base = rtrim((string) config('app.url'), '/').'/api/v1';
        $locales = config('wikiworld.supported_locales', ['ar', 'en']);

        $body = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $body .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($locales as $locale) {
            $body .= '  <sitemap>'."\n";
            $body .= '    <loc>'.e($base.'/sitemap-'.$locale.'.xml').'</loc>'."\n";
            $body .= '  </sitemap>'."\n";
        }

        $body .= '</sitemapindex>';

        return $body;
    }

    public function renderLocaleXml(string $locale): string
    {
        $languageId = Language::query()->where('code', $locale)->value('id');
        $base = rtrim((string) config('app.url'), '/');

        $entries = SitemapEntry::query()
            ->where('is_active', true)
            ->when($languageId, fn ($q) => $q->where('language_id', $languageId))
            ->orderBy('id')
            ->get();

        $body = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $body .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($entries as $entry) {
            $loc = $base.$entry->loc_path;
            $body .= '  <url>'."\n";
            $body .= '    <loc>'.e($loc).'</loc>'."\n";
            $body .= '    <lastmod>'.e($entry->lastmod?->toAtomString() ?? now()->toAtomString()).'</lastmod>'."\n";
            if ($entry->changefreq) {
                $body .= '    <changefreq>'.e($entry->changefreq).'</changefreq>'."\n";
            }
            if ($entry->priority !== null) {
                $body .= '    <priority>'.e((string) $entry->priority).'</priority>'."\n";
            }
            $body .= '  </url>'."\n";
        }

        $body .= '</urlset>';

        return $body;
    }

    public function robotsTxt(): string
    {
        $sitemap = rtrim((string) config('app.url'), '/').'/api/v1/sitemap.xml';

        return implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /api/',
            'Sitemap: '.$sitemap,
            '',
        ]);
    }
}
