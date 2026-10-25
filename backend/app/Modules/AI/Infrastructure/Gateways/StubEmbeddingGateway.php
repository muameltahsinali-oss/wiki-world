<?php

declare(strict_types=1);

namespace App\Modules\AI\Infrastructure\Gateways;

use App\Modules\AI\Application\Contracts\EmbeddingGatewayInterface;

final class StubEmbeddingGateway implements EmbeddingGatewayInterface
{
    public function embed(string $text): array
    {
        $hash = md5($text);
        $vector = [];

        for ($i = 0; $i < 8; $i++) {
            $vector[] = hexdec(substr($hash, $i * 4, 4)) / 65535;
        }

        return $vector;
    }
}
