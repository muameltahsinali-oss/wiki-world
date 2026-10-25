<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Mappers;

final class EntityPublicMapper
{
    /**
     * @param  array<string, mixed>  $view
     * @return array<string, mixed>
     */
    public function map(array $view): array
    {
        return [
            'uuid' => $view['uuid'],
            'type' => $view['entity_type'],
            'title' => $view['title'],
            'slug' => $view['slug'],
            'summary' => $view['summary'],
            'locale' => $view['locale'],
            'direction' => $view['direction'],
            'workflow_state' => $view['workflow_state'],
            'published_at' => $view['published_at'],
            'profile' => $view['profile'] ?? null,
            'blocks' => $view['blocks'] ?? [],
            'relationships_count' => $view['relationships_count'] ?? 0,
            'seo' => [
                'meta_title' => $view['meta_title'] ?? null,
                'meta_description' => $view['meta_description'] ?? null,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $view
     * @return array<string, mixed>
     */
    public function mapListItem(array $view): array
    {
        $item = [
            'uuid' => $view['uuid'],
            'type' => $view['entity_type'],
            'title' => $view['title'],
            'slug' => $view['slug'],
            'summary' => $view['summary'],
            'locale' => $view['locale'],
            'module' => $view['domain'] !== '' ? $view['domain'] : null,
            'published_at' => $view['published_at'] ?? null,
        ];

        if (($view['person_hints'] ?? null) !== null) {
            $item['person_hints'] = $view['person_hints'];
        }

        return $item;
    }
}
