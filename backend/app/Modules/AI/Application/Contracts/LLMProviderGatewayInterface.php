<?php

declare(strict_types=1);

namespace App\Modules\AI\Application\Contracts;

interface LLMProviderGatewayInterface
{
    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     * @return array{content: string, tokens: int, model: string}
     */
    public function chat(array $messages, array $options = []): array;
}
