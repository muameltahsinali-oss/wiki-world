<?php

declare(strict_types=1);

namespace App\Modules\AI\Infrastructure\Gateways;

use App\Modules\AI\Application\Contracts\LLMProviderGatewayInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class OpenAIGateway implements LLMProviderGatewayInterface
{
    public function chat(array $messages, array $options = []): array
    {
        $apiKey = config('wikiworld.ai.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('OPENAI_API_KEY is not configured.');
        }

        $model = (string) ($options['model'] ?? config('wikiworld.ai.model', 'gpt-4o'));

        $request = Http::withToken($apiKey)
            ->timeout(60)
            ->acceptJson();

        if (app()->environment('local')) {
            $request->withoutVerifying();
        }

        $response = $request->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.2,
        ])
            ->throw()
            ->json();

        $content = (string) data_get($response, 'choices.0.message.content', '');
        $tokens = (int) data_get($response, 'usage.total_tokens', 0);

        return [
            'content' => $content,
            'tokens' => $tokens,
            'model' => $model,
        ];
    }
}
