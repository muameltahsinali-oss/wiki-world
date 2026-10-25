<?php

declare(strict_types=1);

namespace App\Modules\Settings\Application\Services;

use App\Modules\Shared\Application\Contracts\CacheGatewayInterface;
use Illuminate\Support\Facades\DB;

final class FeatureToggleService
{
    private const CACHE_PREFIX = 'feature_toggle:';

    /** @var list<string> */
    private const DEFAULT_KEYS = [
        'registration_enabled',
        'ai_enabled',
        'search_enabled',
        'maintenance_mode',
    ];

    public function __construct(
        private readonly CacheGatewayInterface $cache,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        $this->seedDefaults();

        return DB::table('feature_toggles')
            ->orderBy('key')
            ->get()
            ->map(static fn ($row): array => [
                'key' => $row->key,
                'is_enabled' => (bool) $row->is_enabled,
                'updated_at' => $row->updated_at,
            ])
            ->all();
    }

    public function isEnabled(string $key, bool $default = false): bool
    {
        $cacheKey = self::CACHE_PREFIX.$key;

        if ($this->cache->has($cacheKey)) {
            return (bool) $this->cache->get($cacheKey);
        }

        $row = DB::table('feature_toggles')->where('key', $key)->first();
        $enabled = $row !== null
            ? (bool) $row->is_enabled
            : $this->configDefault($key, $default);
        $this->cache->set($cacheKey, $enabled, 3600);

        return $enabled;
    }

    private function configDefault(string $key, bool $default): bool
    {
        return match ($key) {
            'ai_enabled' => (bool) config('wikiworld.ai.enabled', $default),
            'search_enabled' => (bool) config('wikiworld.search.enabled', $default),
            'registration_enabled' => true,
            'maintenance_mode' => false,
            default => $default,
        };
    }

    /**
     * @param  array<string, bool>  $toggles
     * @return list<array<string, mixed>>
     */
    public function updateMany(array $toggles): array
    {
        foreach ($toggles as $key => $enabled) {
            DB::table('feature_toggles')->updateOrInsert(
                ['key' => $key],
                [
                    'is_enabled' => (bool) $enabled,
                    'updated_at' => now(),
                ],
            );
            $this->cache->delete(self::CACHE_PREFIX.$key);
        }

        return $this->all();
    }

    private function seedDefaults(): void
    {
        foreach (self::DEFAULT_KEYS as $key) {
            DB::table('feature_toggles')->insertOrIgnore([
                'key' => $key,
                'is_enabled' => $key !== 'maintenance_mode',
                'updated_at' => now(),
            ]);
        }
    }
}
