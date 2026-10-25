<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Modules\Shared\Application\Contracts\StorageGatewayInterface;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class S3StorageGatewayTest extends TestCase
{
    public function test_storage_gateway_puts_and_gets_files(): void
    {
        Storage::fake('s3');

        $storage = $this->app->make(StorageGatewayInterface::class);

        $storage->put('media/test.txt', 'hello');

        $this->assertTrue($storage->exists('media/test.txt'));
        $this->assertSame('hello', $storage->get('media/test.txt'));
    }
}
