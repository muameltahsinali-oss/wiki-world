<?php

declare(strict_types=1);

namespace App\Modules\Shared\Application\Contracts;

interface CacheGatewayInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value, ?int $ttlSeconds = null): void;

    public function delete(string $key): void;

    public function has(string $key): bool;
}
