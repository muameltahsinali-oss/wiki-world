<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Modules\Shared\Application\Contracts\CacheGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RedisCacheGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_cache_gateway_stores_and_retrieves_values(): void
    {
        $cache = $this->app->make(CacheGatewayInterface::class);

        $cache->set('wikiworld:test', 'value', 60);

        $this->assertTrue($cache->has('wikiworld:test'));
        $this->assertSame('value', $cache->get('wikiworld:test'));

        $cache->delete('wikiworld:test');

        $this->assertFalse($cache->has('wikiworld:test'));
    }
}
