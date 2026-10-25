<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Mappers;

final class EntityEditorialMapper
{
    /**
     * @param  array<string, mixed>  $view
     * @return array<string, mixed>
     */
    public function map(array $view): array
    {
        return [
            'uuid' => $view['uuid'],
            'entity_type' => $view['entity_type'],
            'domain' => $view['domain'],
            'workflow_state' => $view['workflow_state'],
            'locale' => $view['locale'],
            'title' => $view['title'],
            'slug' => $view['slug'],
            'summary' => $view['summary'],
            'translation_status' => $view['translation_status'] ?? null,
            'is_indexable' => $view['is_indexable'] ?? true,
            'published_at' => $view['published_at'] ?? null,
            'version_number' => $view['version_number'] ?? null,
            'profile' => $view['profile'] ?? null,
            'blocks' => $view['blocks'] ?? [],
            'relationships_count' => $view['relationships_count'] ?? 0,
            'meta_title' => $view['meta_title'] ?? null,
            'meta_description' => $view['meta_description'] ?? null,
            'created_at' => $view['created_at'] ?? null,
            'updated_at' => $view['updated_at'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $view
     * @return array<string, mixed>
     */
    public function mapListItem(array $view): array
    {
        return [
            'uuid' => $view['uuid'],
            'entity_type' => $view['entity_type'],
            'domain' => $view['domain'],
            'workflow_state' => $view['workflow_state'],
            'locale' => $view['locale'],
            'title' => $view['title'],
            'slug' => $view['slug'],
            'summary' => $view['summary'],
            'relationships_count' => $view['relationships_count'] ?? 0,
            'updated_at' => $view['updated_at'] ?? null,
        ];
    }
}
