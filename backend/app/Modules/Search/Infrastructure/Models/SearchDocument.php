<?php

declare(strict_types=1);

namespace App\Modules\Search\Infrastructure\Models;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\Users\Infrastructure\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchDocument extends Model
{
    public const UPDATED_AT = 'updated_at';

    public const CREATED_AT = null;

    protected $fillable = [
        'entity_id',
        'language_id',
        'entity_type_code',
        'title',
        'body_text',
        'module_flags',
        'facets',
        'popularity_score',
        'tsv',
        'meilisearch_id',
        'is_active',
        'indexed_at',
    ];

    /**
     * @return BelongsTo<Entity, $this>
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * @return BelongsTo<Language, $this>
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'module_flags' => 'array',
            'facets' => 'array',
            'popularity_score' => 'decimal:4',
            'is_active' => 'boolean',
            'indexed_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
