<?php

declare(strict_types=1);

namespace App\Modules\Settings\Application\Services;

use App\Modules\Shared\Application\Contracts\CacheGatewayInterface;
use Illuminate\Support\Facades\DB;

final class SettingsService
{
    private const CACHE_PREFIX = 'site_setting:';

    public function __construct(
        private readonly CacheGatewayInterface $cache,
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX.$key;

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $row = DB::table('site_settings')->where('key', $key)->first();

        if ($row === null) {
            return $default;
        }

        $value = json_decode($row->value, true, 512, JSON_THROW_ON_ERROR);
        $this->cache->set($cacheKey, $value, 3600);

        return $value;
    }

    public function set(string $key, mixed $value, string $group = 'general', bool $isPublic = false): void
    {
        DB::table('site_settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => json_encode($value, JSON_THROW_ON_ERROR),
                'group' => $group,
                'is_public' => $isPublic,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        $this->cache->delete(self::CACHE_PREFIX.$key);
    }
}
