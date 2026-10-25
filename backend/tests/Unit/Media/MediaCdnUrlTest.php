<?php

declare(strict_types=1);

namespace Tests\Unit\Media;

use App\Modules\Media\Application\Services\MediaService;
use App\Modules\Media\Infrastructure\Models\MediaFile;
use App\Modules\Shared\Application\Contracts\StorageGatewayInterface;
use Tests\TestCase;

final class MediaCdnUrlTest extends TestCase
{
    public function test_media_responses_use_cdn_url_when_configured(): void
    {
        config(['wikiworld.media.cdn_url' => 'https://cdn.wikiworld.test']);

        $storage = \Mockery::mock(StorageGatewayInterface::class);
        $storage->shouldReceive('url')->never();
        $this->app->instance(StorageGatewayInterface::class, $storage);

        $media = new MediaFile([
            'uuid' => '11111111-1111-4111-8111-111111111111',
            'storage_path' => 'media/2026/07/test.jpg',
            'media_type' => 'image',
            'status' => 'ready',
        ]);
        $media->setRelation('variants', collect());

        $service = $this->app->make(MediaService::class);
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('mapMedia');
        $method->setAccessible(true);

        /** @var array<string, mixed> $mapped */
        $mapped = $method->invoke($service, $media);

        $this->assertSame('https://cdn.wikiworld.test/media/2026/07/test.jpg', $mapped['url']);
    }
}
