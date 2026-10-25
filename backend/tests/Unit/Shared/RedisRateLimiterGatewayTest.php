<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Modules\Shared\Application\Contracts\RateLimiterGatewayInterface;
use Tests\TestCase;

final class RedisRateLimiterGatewayTest extends TestCase
{
    public function test_rate_limiter_tracks_attempts(): void
    {
        $limiter = $this->app->make(RateLimiterGatewayInterface::class);
        $key = 'wikiworld:test:rate';

        $limiter->clear($key);
        $limiter->hit($key, 60);

        $this->assertFalse($limiter->tooManyAttempts($key, 2));
        $this->assertSame(1, $limiter->remaining($key, 2));
    }
}
