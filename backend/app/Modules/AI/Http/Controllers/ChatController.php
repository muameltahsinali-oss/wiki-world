<?php

declare(strict_types=1);

namespace App\Modules\AI\Http\Controllers;

use App\Modules\AI\Application\Services\AiChatService;
use App\Modules\AI\Domain\Exceptions\AiRateLimitExceededException;
use App\Modules\AI\Domain\Exceptions\InsufficientCorpusException;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ChatController
{
    public function __construct(
        private readonly AiChatService $chat,
    ) {}

    public function __invoke(Request $request): JsonResponse|StreamedResponse
    {
        if (! config('wikiworld.ai.enabled', false)) {
            return ApiResponse::error('AI_UNAVAILABLE', 'AI is disabled.', 503);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:4000'],
            'locale' => ['nullable', 'string', 'in:ar,en'],
            'conversation_uuid' => ['nullable', 'uuid'],
            'context_entity_uuid' => ['nullable', 'uuid'],
        ]);

        $locale = $validated['locale'] ?? config('wikiworld.default_locale', 'ar');
        /** @var User|null $user */
        $user = $request->user('sanctum');
        $clientKey = sha1((string) $request->ip());

        try {
            $result = $this->chat->chat(
                $validated['message'],
                $locale,
                $user,
                $validated['conversation_uuid'] ?? null,
                $validated['context_entity_uuid'] ?? null,
                $clientKey,
            );
        } catch (InsufficientCorpusException $e) {
            return ApiResponse::error('INSUFFICIENT_CORPUS', $e->getMessage(), 422);
        } catch (AiRateLimitExceededException $e) {
            return ApiResponse::error('RATE_LIMIT_EXCEEDED', $e->getMessage(), 429);
        }

        $wantsStream = str_contains((string) $request->header('Accept'), 'text/event-stream');

        if ($wantsStream) {
            return response()->stream(function () use ($result): void {
                $answer = (string) $result['answer'];
                $chunks = mb_str_split($answer, 40);

                foreach ($chunks as $chunk) {
                    echo 'event: delta'."\n";
                    echo 'data: '.json_encode(['delta' => $chunk], JSON_UNESCAPED_UNICODE)."\n\n";
                    if (function_exists('ob_flush')) {
                        @ob_flush();
                    }
                    flush();
                }

                echo 'event: done'."\n";
                echo 'data: '.json_encode([
                    'conversation_uuid' => $result['conversation_uuid'],
                    'message_uuid' => $result['message_uuid'],
                    'citations' => $result['citations'],
                    'disclaimer' => $result['disclaimer'],
                ], JSON_UNESCAPED_UNICODE)."\n\n";
                if (function_exists('ob_flush')) {
                    @ob_flush();
                }
                flush();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        return ApiResponse::success([
            'conversation_uuid' => $result['conversation_uuid'],
            'message_uuid' => $result['message_uuid'],
            'answer' => $result['answer'],
            'citations' => $result['citations'],
            'disclaimer' => $result['disclaimer'],
            'related_entities' => $result['related_entities'],
            'refused' => $result['refused'],
        ], meta: [
            'model' => $result['model'],
            'tokens_used' => $result['tokens_used'],
        ]);
    }
}
