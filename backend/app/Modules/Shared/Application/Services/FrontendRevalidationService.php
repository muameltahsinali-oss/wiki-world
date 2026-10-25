<?php

declare(strict_types=1);

namespace App\Modules\Shared\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class FrontendRevalidationService
{
    /**
     * @return list<string>
     */
    public function pathsForEntity(int $entityId, ?string $locale = null): array
    {
        $typeCode = DB::table('entities')
            ->join('entity_types', 'entity_types.id', '=', 'entities.entity_type_id')
            ->where('entities.id', $entityId)
            ->value('entity_types.code');

        if (! is_string($typeCode) || $typeCode === '') {
            return [];
        }

        $uuid = DB::table('entities')->where('id', $entityId)->value('uuid');
        if (! is_string($uuid)) {
            return [];
        }

        $locales = config('wikiworld.supported_locales', ['ar', 'en']);
        if ($locale !== null) {
            $locales = in_array($locale, $locales, true) ? [$locale] : $locales;
        }

        $paths = [];
        foreach ($locales as $code) {
            $paths[] = sprintf('/%s/%ss/%s', $code, $typeCode, $uuid);
        }

        return $paths;
    }

    /**
     * @param  list<string>  $paths
     */
    public function revalidate(array $paths): void
    {
        $baseUrl = rtrim((string) config('wikiworld.frontend.url'), '/');
        $secret = (string) config('wikiworld.frontend.revalidate_secret');

        if ($baseUrl === '' || $secret === '' || $paths === []) {
            return;
        }

        $response = Http::timeout(5)->post($baseUrl.'/api/revalidate?secret='.urlencode($secret), [
            'paths' => $paths,
        ]);

        if (! $response->successful()) {
            Log::warning('frontend.revalidate_failed', [
                'status' => $response->status(),
                'paths' => $paths,
            ]);
        }
    }
}
