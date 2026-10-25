<?php

declare(strict_types=1);

namespace App\Modules\SEO\Infrastructure\Models;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\Users\Infrastructure\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoMetadata extends Model
{
    public const CREATED_AT = null;

    protected $table = 'seo_metadata';

    protected $fillable = [
        'entity_id',
        'language_id',
        'meta_title',
        'meta_description',
        'canonical_override',
        'robots',
        'og_image_uuid',
        'structured_data',
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
            'structured_data' => 'array',
            'updated_at' => 'datetime',
        ];
    }
}
