<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntityLocalization extends Model
{
    protected $fillable = [
        'entity_id',
        'language_id',
        'title',
        'slug',
        'summary',
        'meta_title',
        'meta_description',
        'og_image_media_id',
        'translation_status',
        'workflow_state',
        'published_at',
    ];

    /**
     * @return BelongsTo<Entity, $this>
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }
}
