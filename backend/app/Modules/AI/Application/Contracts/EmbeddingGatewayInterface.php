<?php

declare(strict_types=1);

namespace App\Modules\AI\Application\Contracts;

interface EmbeddingGatewayInterface
{
    /**
     * @return list<float>
     */
    public function embed(string $text): array;
}
