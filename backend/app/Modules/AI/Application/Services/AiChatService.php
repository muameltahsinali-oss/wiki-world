<?php

declare(strict_types=1);

namespace App\Modules\AI\Application\Services;

use App\Modules\AI\Domain\Exceptions\AiRateLimitExceededException;
use App\Modules\AI\Infrastructure\Models\AiConversation;
use App\Modules\AI\Infrastructure\Models\AiMessage;
use App\Modules\AI\Infrastructure\Models\AiResponseFlag;
use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityLocalization;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityRelationship;
use App\Modules\Shared\Application\Contracts\RateLimiterGatewayInterface;
use App\Modules\Users\Infrastructure\Models\Language;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class AiChatService
{
    public function __construct(
        private readonly RagPipelineService $rag,
        private readonly RateLimiterGatewayInterface $rateLimiter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function chat(
        string $message,
        string $locale,
        ?User $user,
        ?string $conversationUuid,
        ?string $contextEntityUuid,
        string $clientKey,
    ): array {
        $this->enforceRateLimit($user, $clientKey);

        return $this->rag->answer($message, $locale, $user, $conversationUuid, $contextEntityUuid);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listConversations(User $user): array
    {
        return AiConversation::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(static fn (AiConversation $c): array => [
                'uuid' => $c->uuid,
                'title' => $c->title,
                'created_at' => $c->created_at?->toIso8601String(),
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function getConversation(User $user, string $uuid): array
    {
        $conversation = AiConversation::query()
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->first();

        if ($conversation === null) {
            throw (new ModelNotFoundException)->setModel(AiConversation::class, [$uuid]);
        }

        $messages = AiMessage::query()
            ->where('conversation_id', $conversation->id)
            ->orderBy('id')
            ->get()
            ->map(static fn (AiMessage $m): array => [
                'uuid' => $m->uuid,
                'role' => $m->role,
                'content' => $m->content,
                'created_at' => $m->created_at?->toIso8601String(),
            ])
            ->all();

        return [
            'uuid' => $conversation->uuid,
            'title' => $conversation->title,
            'messages' => $messages,
        ];
    }

    public function deleteConversation(User $user, string $uuid): void
    {
        $conversation = AiConversation::query()
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->first();

        if ($conversation === null) {
            throw (new ModelNotFoundException)->setModel(AiConversation::class, [$uuid]);
        }

        $conversation->delete();
    }

    /**
     * @return array{uuid: string, status: string}
     */
    public function flagMessage(string $messageUuid, ?User $user, ?string $comment): array
    {
        $message = AiMessage::query()->where('uuid', $messageUuid)->first();

        if ($message === null) {
            throw (new ModelNotFoundException)->setModel(AiMessage::class, [$messageUuid]);
        }

        $flag = AiResponseFlag::query()->create([
            'message_id' => $message->id,
            'flagged_by_user_id' => $user?->id,
            'comment' => $comment,
            'status' => 'open',
        ]);

        return [
            'uuid' => (string) $flag->id,
            'status' => $flag->status,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listFlags(int $limit = 50): array
    {
        return AiResponseFlag::query()
            ->with('message')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(static fn (AiResponseFlag $flag): array => [
                'id' => $flag->id,
                'message_uuid' => $flag->message?->uuid,
                'comment' => $flag->comment,
                'status' => $flag->status,
                'resolved_at' => $flag->resolved_at?->toIso8601String(),
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveFlag(int $flagId, User $resolver, string $status): array
    {
        $flag = AiResponseFlag::query()->find($flagId);

        if ($flag === null) {
            throw (new ModelNotFoundException)->setModel(AiResponseFlag::class, [$flagId]);
        }

        $flag->status = $status;
        $flag->resolved_by_user_id = $resolver->id;
        $flag->resolved_at = now();
        $flag->save();

        return [
            'id' => $flag->id,
            'status' => $flag->status,
            'resolved_at' => $flag->resolved_at?->toIso8601String(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recommendations(string $locale, ?string $entityUuid, int $limit = 5): array
    {
        $limit = min(10, max(1, $limit));
        $languageId = Language::query()->where('code', $locale)->value('id');

        if ($entityUuid !== null) {
            $entity = Entity::query()->where('uuid', $entityUuid)->first();
            if ($entity !== null) {
                $relatedIds = EntityRelationship::query()
                    ->where(function ($q) use ($entity): void {
                        $q->where('source_entity_id', $entity->id)
                            ->orWhere('target_entity_id', $entity->id);
                    })
                    ->limit($limit)
                    ->get()
                    ->map(static fn ($rel) => $rel->source_entity_id === $entity->id
                        ? (int) $rel->target_entity_id
                        : (int) $rel->source_entity_id)
                    ->unique()
                    ->all();

                if ($relatedIds !== []) {
                    return Entity::query()
                        ->with('entityType')
                        ->whereIn('id', $relatedIds)
                        ->where('workflow_state', 'published')
                        ->limit($limit)
                        ->get()
                        ->map(function (Entity $e) use ($languageId): array {
                            $loc = EntityLocalization::query()
                                ->where('entity_id', $e->id)
                                ->when($languageId, fn ($q) => $q->where('language_id', $languageId))
                                ->first();

                            return [
                                'uuid' => $e->uuid,
                                'type' => $e->entityType?->code,
                                'title' => $loc?->title,
                            ];
                        })
                        ->all();
                }
            }
        }

        return Entity::query()
            ->with('entityType')
            ->where('workflow_state', 'published')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get()
            ->map(function (Entity $e) use ($languageId): array {
                $loc = EntityLocalization::query()
                    ->where('entity_id', $e->id)
                    ->when($languageId, fn ($q) => $q->where('language_id', $languageId))
                    ->first();

                return [
                    'uuid' => $e->uuid,
                    'type' => $e->entityType?->code,
                    'title' => $loc?->title,
                ];
            })
            ->all();
    }

    private function enforceRateLimit(?User $user, string $clientKey): void
    {
        $max = $user !== null
            ? (int) config('wikiworld.ai.rate_limit.registered', 50)
            : (int) config('wikiworld.ai.rate_limit.guest', 10);

        $key = $user !== null
            ? 'ai:chat:user:'.$user->id
            : 'ai:chat:guest:'.$clientKey;

        if ($this->rateLimiter->tooManyAttempts($key, $max)) {
            throw new AiRateLimitExceededException;
        }

        $this->rateLimiter->hit($key, 3600);
    }
}
