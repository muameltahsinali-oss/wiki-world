<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\Cache;

use App\Modules\Shared\Application\Contracts\CacheGatewayInterface;
use Illuminate\Support\Facades\Cache;

final class RedisCacheGateway implements CacheGatewayInterface
{
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::get($key, $default);
    }

    public function set(string $key, mixed $value, ?int $ttlSeconds = null): void
    {
        if ($ttlSeconds === null) {
            Cache::forever($key, $value);

            return;
        }

        Cache::put($key, $value, $ttlSeconds);
    }

    public function delete(string $key): void
    {
        Cache::forget($key);
    }

    public function has(string $key): bool
    {
        return Cache::has($key);
    }
}
