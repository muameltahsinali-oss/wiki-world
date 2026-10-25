<?php

declare(strict_types=1);

namespace App\Modules\AI\Infrastructure\Gateways;

use App\Modules\AI\Application\Contracts\LLMProviderGatewayInterface;

final class FakeLLMGateway implements LLMProviderGatewayInterface
{
    public function chat(array $messages, array $options = []): array
    {
        $userMessage = '';
        foreach (array_reverse($messages) as $message) {
            if (($message['role'] ?? '') === 'user') {
                $userMessage = (string) ($message['content'] ?? '');
                break;
            }
        }

        $corpusTitles = [];
        if (isset($options['corpus']) && is_array($options['corpus'])) {
            foreach ($options['corpus'] as $item) {
                if (isset($item['title']) && is_string($item['title'])) {
                    $corpusTitles[] = $item['title'];
                }
            }
        }

        $citations = $corpusTitles === []
            ? 'No corpus titles available.'
            : 'Based on: '.implode(', ', array_map(static fn (string $t): string => '['.$t.']', $corpusTitles)).'.';

        $content = 'Educational answer regarding your question. '.$citations
            .' This response is for educational purposes only and is not a religious ruling.';

        return [
            'content' => $content,
            'tokens' => 120 + mb_strlen($userMessage),
            'model' => 'fake-llm',
        ];
    }
}
