<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\Storage;

use App\Modules\Shared\Application\Contracts\StorageGatewayInterface;
use Illuminate\Support\Facades\Storage;

final class S3StorageGateway implements StorageGatewayInterface
{
    public function __construct(
        private readonly ?string $disk = null,
    ) {}

    private function diskName(): string
    {
        return $this->disk ?? (string) config('filesystems.default', 'local');
    }

    public function put(string $path, string $contents, array $options = []): bool
    {
        return Storage::disk($this->diskName())->put($path, $contents, $options);
    }

    public function get(string $path): ?string
    {
        if (! $this->exists($path)) {
            return null;
        }

        return Storage::disk($this->diskName())->get($path);
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->diskName())->delete($path);
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->diskName())->exists($path);
    }

    public function url(string $path): string
    {
        return Storage::disk($this->diskName())->url($path);
    }
}
