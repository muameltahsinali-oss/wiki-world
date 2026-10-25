<?php

declare(strict_types=1);

namespace App\Modules\Shared\Application\Contracts;

interface StorageGatewayInterface
{
    public function put(string $path, string $contents, array $options = []): bool;

    public function get(string $path): ?string;

    public function delete(string $path): bool;

    public function exists(string $path): bool;

    public function url(string $path): string;
}
