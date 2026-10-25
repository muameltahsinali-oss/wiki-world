<?php

declare(strict_types=1);

namespace App\Modules\Publishing\Application\Services;

use App\Modules\KnowledgeCore\Application\Services\EntityLocalizationService;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityLocalization;
use Illuminate\Support\Facades\DB;

final class PublishIntegrityValidator
{
    private const MIN_SOURCES = 1;

    public function __construct(
        private readonly EntityLocalizationService $localizations,
    ) {}

    public function validate(int $entityId, string $locale): void
    {
        $readiness = $this->checkReadiness($entityId, $locale);

        foreach ($readiness['checks'] as $check) {
            if (! $check['passed']) {
                throw new \InvalidArgumentException($check['message'] ?? 'Publish readiness check failed: '.$check['code']);
            }
        }
    }

    /**
     * @return array{ready: bool, checks: list<array<string, mixed>>}
     */
    public function checkReadiness(int $entityId, string $locale): array
    {
        $localization = $this->localizations->getByEntityAndLocale($entityId, $locale);
        $referenceCount = (int) DB::table('references')->where('entity_id', $entityId)->count();

        $checks = [
            [
                'code' => 'title_present',
                'passed' => $localization !== null && trim((string) $localization->title) !== '',
                'message' => 'Title is required before publishing.',
            ],
            [
                'code' => 'slug_present',
                'passed' => $localization !== null && trim((string) $localization->slug) !== '',
                'message' => 'Slug is required before publishing.',
            ],
            [
                'code' => 'min_sources',
                'passed' => $referenceCount >= self::MIN_SOURCES,
                'required' => self::MIN_SOURCES,
                'actual' => $referenceCount,
                'message' => 'At least '.self::MIN_SOURCES.' reference is required.',
            ],
            [
                'code' => 'slug_unique',
                'passed' => $this->isSlugUnique($localization),
                'message' => 'Slug conflicts with another entity in this locale.',
            ],
        ];

        $ready = collect($checks)->every(static fn (array $check): bool => $check['passed']);

        return [
            'ready' => $ready,
            'checks' => $checks,
        ];
    }

    private function isSlugUnique(?EntityLocalization $localization): bool
    {
        if ($localization === null || $localization->slug === null || $localization->slug === '') {
            return false;
        }

        return ! EntityLocalization::query()
            ->where('language_id', $localization->language_id)
            ->where('slug', $localization->slug)
            ->where('id', '!=', $localization->id)
            ->exists();
    }
}
