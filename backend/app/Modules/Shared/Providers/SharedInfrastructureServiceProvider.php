<?php

declare(strict_types=1);

namespace App\Modules\Shared\Providers;

use App\Modules\Shared\Application\Contracts\CacheGatewayInterface;
use App\Modules\Shared\Application\Contracts\RateLimiterGatewayInterface;
use App\Modules\Shared\Application\Contracts\StorageGatewayInterface;
use App\Modules\Shared\Application\Services\FrontendRevalidationService;
use App\Modules\Shared\Infrastructure\Cache\RedisCacheGateway;
use App\Modules\Shared\Infrastructure\RateLimit\RedisRateLimiterGateway;
use App\Modules\Shared\Infrastructure\Storage\S3StorageGateway;
use Illuminate\Support\ServiceProvider;

final class SharedInfrastructureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CacheGatewayInterface::class, RedisCacheGateway::class);
        $this->app->singleton(StorageGatewayInterface::class, S3StorageGateway::class);
        $this->app->singleton(RateLimiterGatewayInterface::class, RedisRateLimiterGateway::class);
        $this->app->singleton(FrontendRevalidationService::class);
    }
}
