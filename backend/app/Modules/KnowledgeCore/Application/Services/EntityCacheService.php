<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Services;

use App\Modules\Shared\Application\Contracts\CacheGatewayInterface;

final class EntityCacheService
{
    private const TTL_SECONDS = 900;

    public function __construct(
        private readonly CacheGatewayInterface $cache,
    ) {}

    public function key(string $uuid, string $locale): string
    {
        return 'entity:'.$uuid.':'.$locale;
    }

    public function get(string $uuid, string $locale): mixed
    {
        return $this->cache->get($this->key($uuid, $locale));
    }

    public function set(string $uuid, string $locale, mixed $value): void
    {
        $this->cache->set($this->key($uuid, $locale), $value, self::TTL_SECONDS);
    }

    public function forget(string $uuid, string $locale): void
    {
        $this->cache->delete($this->key($uuid, $locale));
    }
}
