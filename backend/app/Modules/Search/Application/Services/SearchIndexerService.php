<?php

declare(strict_types=1);

namespace App\Modules\Search\Application\Services;

use App\Modules\KnowledgeCore\Infrastructure\Models\ContentBlock;
use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\KnowledgeCore\Infrastructure\Models\EntityLocalization;
use App\Modules\Search\Application\Contracts\SearchIndexGatewayInterface;
use App\Modules\Search\Infrastructure\Models\SearchDocument;
use App\Modules\Users\Infrastructure\Models\Language;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SearchIndexerService
{
    public function __construct(
        private readonly SearchIndexGatewayInterface $gateway,
    ) {}

    public function indexEntity(int $entityId, ?string $locale = null): void
    {
        $entity = Entity::query()->with('entityType')->find($entityId);

        if ($entity === null || ! $entity->is_indexable) {
            return;
        }

        $localizations = EntityLocalization::query()
            ->where('entity_id', $entityId)
            ->when($locale !== null, function ($q) use ($locale): void {
                $languageId = Language::query()->where('code', $locale)->value('id');
                if ($languageId !== null) {
                    $q->where('language_id', $languageId);
                }
            })
            ->get();

        foreach ($localizations as $localization) {
            $this->upsertDocument($entity, $localization);
        }
    }

    public function deactivateEntity(int $entityId): void
    {
        $documents = SearchDocument::query()->where('entity_id', $entityId)->get();

        foreach ($documents as $document) {
            $document->is_active = false;
            $document->save();

            if (is_string($document->meilisearch_id) && $document->meilisearch_id !== '') {
                try {
                    $this->gateway->delete($document->meilisearch_id);
                } catch (Throwable $e) {
                    Log::warning('Search deactivate gateway delete failed: '.$e->getMessage());
                }
            }
        }
    }

    private function upsertDocument(Entity $entity, EntityLocalization $localization): void
    {
        $language = Language::query()->find($localization->language_id);
        $typeCode = $entity->entityType?->code ?? 'unknown';
        $moduleFlags = is_array($entity->module_flags) ? $entity->module_flags : [];
        $bodyText = $this->aggregateBodyText($entity->id, $localization);
        $meilisearchId = $entity->uuid.':'.($language?->code ?? 'und');

        $primaryModule = $this->resolvePrimaryModule($moduleFlags);

        $document = SearchDocument::query()->updateOrCreate(
            ['entity_id' => $entity->id],
            [
                'language_id' => $localization->language_id,
                'entity_type_code' => $typeCode,
                'title' => $localization->title,
                'body_text' => $bodyText,
                'module_flags' => $moduleFlags,
                'facets' => [
                    'type' => $typeCode,
                    'module' => $primaryModule,
                ],
                'popularity_score' => 0,
                'meilisearch_id' => $meilisearchId,
                'is_active' => true,
                'indexed_at' => now(),
                'updated_at' => now(),
            ],
        );

        try {
            $this->gateway->index([
                'id' => $meilisearchId,
                'entity_id' => $entity->id,
                'uuid' => $entity->uuid,
                'title' => $document->title,
                'body_text' => $document->body_text,
                'entity_type_code' => $typeCode,
                'module' => $primaryModule,
                'module_flags' => $moduleFlags,
                'locale' => $language?->code,
                'facets' => $document->facets,
            ]);
        } catch (Throwable $e) {
            Log::warning('Search gateway index failed (local document retained): '.$e->getMessage());
        }
    }

    private function aggregateBodyText(int $entityId, EntityLocalization $localization): string
    {
        $parts = array_filter([
            $localization->title,
            $localization->summary,
        ]);

        $blocks = ContentBlock::query()
            ->where('entity_id', $entityId)
            ->where('language_id', $localization->language_id)
            ->orderBy('sort_order')
            ->get();

        foreach ($blocks as $block) {
            $content = $block->content;
            if (is_array($content)) {
                if (isset($content['text']) && is_string($content['text'])) {
                    $parts[] = $content['text'];
                } elseif (isset($content['content']) && is_string($content['content'])) {
                    $parts[] = $content['content'];
                } else {
                    $parts[] = strip_tags(json_encode($content, JSON_UNESCAPED_UNICODE) ?: '');
                }
            } elseif (is_string($content)) {
                $parts[] = $content;
            }
        }

        return implode("\n", $parts);
    }

    /**
     * @param  array<string, mixed>  $moduleFlags
     */
    private function resolvePrimaryModule(array $moduleFlags): string
    {
        foreach (['quran', 'ahlulbayt', 'general', 'books_sources'] as $module) {
            if (($moduleFlags[$module] ?? false) === true) {
                return $module;
            }
        }

        return 'general';
    }
}
