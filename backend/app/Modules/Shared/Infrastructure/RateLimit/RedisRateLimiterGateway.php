<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\RateLimit;

use App\Modules\Shared\Application\Contracts\RateLimiterGatewayInterface;
use Illuminate\Support\Facades\RateLimiter;

final class RedisRateLimiterGateway implements RateLimiterGatewayInterface
{
    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    public function hit(string $key, int $decaySeconds): int
    {
        return RateLimiter::hit($key, $decaySeconds);
    }

    public function remaining(string $key, int $maxAttempts): int
    {
        return RateLimiter::remaining($key, $maxAttempts);
    }

    public function clear(string $key): void
    {
        RateLimiter::clear($key);
    }
}
