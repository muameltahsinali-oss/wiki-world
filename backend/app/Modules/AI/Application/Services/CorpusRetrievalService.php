<?php

declare(strict_types=1);

namespace App\Modules\AI\Application\Services;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\Search\Infrastructure\Models\SearchDocument;
use App\Modules\Users\Infrastructure\Models\Language;

final class CorpusRetrievalService
{
    /**
     * @return list<array{uuid: string, title: string, summary: ?string, type: string, entity_id: int, excerpt: string}>
     */
    public function retrieve(string $query, string $locale, int $limit = 5): array
    {
        $languageId = Language::query()->where('code', $locale)->value('id');
        $tokens = $this->tokens($query);

        $builder = SearchDocument::query()
            ->where('is_active', true)
            ->when($languageId, fn ($q) => $q->where('language_id', $languageId));

        if ($tokens !== []) {
            $builder->where(function ($outer) use ($tokens): void {
                foreach ($tokens as $token) {
                    $escaped = addcslashes($token, '%_\\');
                    $outer->orWhere(function ($q) use ($escaped): void {
                        $q->where('title', 'like', '%'.$escaped.'%')
                            ->orWhere('body_text', 'like', '%'.$escaped.'%');
                    });
                }
            });
        }

        $documents = $builder
            ->orderByDesc('popularity_score')
            ->limit($limit)
            ->get();

        if ($documents->isEmpty() && $tokens !== []) {
            $documents = SearchDocument::query()
                ->where('is_active', true)
                ->when($languageId, fn ($q) => $q->where('language_id', $languageId))
                ->orderByDesc('popularity_score')
                ->limit($limit)
                ->get();
        }

        $entityIds = $documents->pluck('entity_id')->all();
        $entities = Entity::query()->whereIn('id', $entityIds)->get()->keyBy('id');

        $localizations = \App\Modules\KnowledgeCore\Infrastructure\Models\EntityLocalization::query()
            ->whereIn('entity_id', $entityIds)
            ->where('language_id', $languageId)
            ->get()
            ->keyBy('entity_id');

        return $documents->map(function (SearchDocument $doc) use ($entities, $localizations): array {
            $entity = $entities->get($doc->entity_id);
            $loc = $localizations->get($doc->entity_id);

            return [
                'uuid' => (string) ($entity?->uuid ?? ''),
                'title' => $doc->title,
                'summary' => mb_substr((string) ($doc->body_text ?? ''), 0, 240),
                'type' => $doc->entity_type_code,
                'entity_id' => (int) $doc->entity_id,
                'excerpt' => mb_substr((string) ($doc->body_text ?? $doc->title), 0, 200),
                'slug' => (string) ($loc?->slug ?? ''),
            ];
        })->filter(static fn (array $row): bool => $row['uuid'] !== '')->values()->all();
    }

    /**
     * @return list<string>
     */
    private function tokens(string $query): array
    {
        $parts = preg_split('/[\s\?\!\.\,\;\:\"\'\(\)]+/u', mb_strtolower($query)) ?: [];

        $tokens = array_values(array_filter(
            $parts,
            static fn (string $part): bool => mb_strlen($part) >= 3
                && ! in_array($part, ['the', 'and', 'what', 'is', 'are', 'for', 'about', 'this', 'that'], true),
        ));

        return array_slice($tokens, 0, 8);
    }
}
