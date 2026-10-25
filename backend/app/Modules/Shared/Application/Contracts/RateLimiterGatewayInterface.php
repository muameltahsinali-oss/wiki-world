<?php

declare(strict_types=1);

namespace App\Modules\Shared\Application\Contracts;

interface RateLimiterGatewayInterface
{
    public function tooManyAttempts(string $key, int $maxAttempts): bool;

    public function hit(string $key, int $decaySeconds): int;

    public function remaining(string $key, int $maxAttempts): int;

    public function clear(string $key): void;
}
