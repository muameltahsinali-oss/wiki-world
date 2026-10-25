<?php

declare(strict_types=1);

namespace App\Modules\AI\Application\Services;

use App\Modules\AI\Application\Contracts\LLMProviderGatewayInterface;
use App\Modules\AI\Domain\Exceptions\InsufficientCorpusException;
use App\Modules\AI\Infrastructure\Models\AiCitationLink;
use App\Modules\AI\Infrastructure\Models\AiConversation;
use App\Modules\AI\Infrastructure\Models\AiMessage;
use App\Modules\Users\Infrastructure\Models\Language;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

final class RagPipelineService
{
    public function __construct(
        private readonly CorpusRetrievalService $corpus,
        private readonly RefusalPolicyHandler $refusal,
        private readonly LLMProviderGatewayInterface $llm,
    ) {}

    /**
     * @return array{
     *     conversation_uuid: string,
     *     message_uuid: string,
     *     answer: string,
     *     citations: list<array<string, mixed>>,
     *     disclaimer: string,
     *     related_entities: list<array<string, mixed>>,
     *     refused: bool,
     *     model: string,
     *     tokens_used: int
     * }
     */
    public function answer(
        string $message,
        string $locale,
        ?User $user = null,
        ?string $conversationUuid = null,
        ?string $contextEntityUuid = null,
    ): array {
        if ($this->refusal->shouldRefuse($message)) {
            $refusal = $this->refusal->refusalResponse();
            $persisted = $this->persistTurn($user, $locale, $conversationUuid, $message, $refusal['answer'], []);

            return [
                'conversation_uuid' => $persisted['conversation_uuid'],
                'message_uuid' => $persisted['message_uuid'],
                'answer' => $refusal['answer'],
                'citations' => [],
                'disclaimer' => RefusalPolicyHandler::DISCLAIMER,
                'related_entities' => [],
                'refused' => true,
                'model' => 'policy',
                'tokens_used' => 0,
            ];
        }

        $retrieved = $this->corpus->retrieve($message, $locale, 5);

        if ($retrieved === []) {
            throw new InsufficientCorpusException;
        }

        $promptMessages = [
            [
                'role' => 'system',
                'content' => 'You are an educational assistant for Wiki World. Answer only from the provided corpus. Always cite sources by title. Never issue fatwas. End with a disclaimer that content is educational, not a religious ruling.',
            ],
            [
                'role' => 'user',
                'content' => $this->buildUserPrompt($message, $retrieved, $contextEntityUuid),
            ],
        ];

        $llmResult = $this->llm->chat($promptMessages, [
            'corpus' => $retrieved,
        ]);

        $citations = array_map(static fn (array $item, int $index): array => [
            'entity_uuid' => $item['uuid'],
            'entity_type' => $item['type'],
            'title' => $item['title'],
            'excerpt' => $item['excerpt'],
            'source_uuid' => null,
            'confidence' => max(0.5, 0.95 - ($index * 0.05)),
            'entity_id' => $item['entity_id'],
            'slug' => $item['slug'] ?? null,
        ], $retrieved, array_keys($retrieved));

        $answer = trim($llmResult['content']);
        if (! str_contains(mb_strtolower($answer), 'not a religious ruling')
            && ! str_contains($answer, RefusalPolicyHandler::DISCLAIMER)) {
            $answer .= "\n\n".RefusalPolicyHandler::DISCLAIMER;
        }

        $persisted = $this->persistTurn($user, $locale, $conversationUuid, $message, $answer, $citations);

        return [
            'conversation_uuid' => $persisted['conversation_uuid'],
            'message_uuid' => $persisted['message_uuid'],
            'answer' => $answer,
            'citations' => array_map(static function (array $c): array {
                unset($c['entity_id']);

                return $c;
            }, $citations),
            'disclaimer' => RefusalPolicyHandler::DISCLAIMER,
            'related_entities' => array_map(static fn (array $item): array => [
                'uuid' => $item['uuid'],
                'type' => $item['type'],
                'title' => $item['title'],
            ], array_slice($retrieved, 0, 3)),
            'refused' => false,
            'model' => $llmResult['model'],
            'tokens_used' => $llmResult['tokens'],
        ];
    }

    /**
     * @param  list<array{uuid: string, title: string, summary: ?string, type: string, entity_id: int, excerpt: string}>  $retrieved
     */
    private function buildUserPrompt(string $message, array $retrieved, ?string $contextEntityUuid): string
    {
        $corpusBlock = collect($retrieved)
            ->map(static fn (array $item, int $i): string => ($i + 1).'. ['.$item['title'].'] ('.$item['type'].'): '.$item['excerpt'])
            ->implode("\n");

        $context = $contextEntityUuid ? "Context entity: {$contextEntityUuid}\n" : '';

        return $context."Question: {$message}\n\nPublished corpus:\n{$corpusBlock}";
    }

    /**
     * @param  list<array<string, mixed>>  $citations
     * @return array{conversation_uuid: string, message_uuid: string}
     */
    private function persistTurn(
        ?User $user,
        string $locale,
        ?string $conversationUuid,
        string $userMessage,
        string $assistantMessage,
        array $citations,
    ): array {
        return DB::transaction(function () use ($user, $locale, $conversationUuid, $userMessage, $assistantMessage, $citations): array {
            $languageId = (int) Language::query()->where('code', $locale)->value('id');

            $conversation = null;
            if ($conversationUuid !== null && $user !== null) {
                $conversation = AiConversation::query()
                    ->where('uuid', $conversationUuid)
                    ->where('user_id', $user->id)
                    ->first();
            }

            if ($conversation === null && $user !== null) {
                $conversation = AiConversation::query()->create([
                    'uuid' => Uuid::uuid7()->toString(),
                    'user_id' => $user->id,
                    'language_id' => $languageId,
                    'title' => mb_substr($userMessage, 0, 120),
                ]);
            }

            if ($conversation === null) {
                // Anonymous: ephemeral conversation uuid (still create row without user for flag linkage if needed)
                $conversation = AiConversation::query()->create([
                    'uuid' => Uuid::uuid7()->toString(),
                    'user_id' => null,
                    'language_id' => $languageId,
                    'title' => mb_substr($userMessage, 0, 120),
                ]);
            }

            AiMessage::query()->create([
                'uuid' => Uuid::uuid7()->toString(),
                'conversation_id' => $conversation->id,
                'role' => 'user',
                'content' => $userMessage,
            ]);

            $assistant = AiMessage::query()->create([
                'uuid' => Uuid::uuid7()->toString(),
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $assistantMessage,
            ]);

            foreach ($citations as $index => $citation) {
                if (! isset($citation['entity_id'])) {
                    continue;
                }
                AiCitationLink::query()->create([
                    'message_id' => $assistant->id,
                    'entity_id' => $citation['entity_id'],
                    'sort_order' => $index,
                ]);
            }

            return [
                'conversation_uuid' => $conversation->uuid,
                'message_uuid' => $assistant->uuid,
            ];
        });
    }
}
