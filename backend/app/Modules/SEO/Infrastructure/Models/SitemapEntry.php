<?php

declare(strict_types=1);

namespace App\Modules\SEO\Infrastructure\Models;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\Users\Infrastructure\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitemapEntry extends Model
{
    public $timestamps = false;

    protected $table = 'sitemap_entries';

    protected $fillable = [
        'entity_id',
        'language_id',
        'loc_path',
        'lastmod',
        'changefreq',
        'priority',
        'is_active',
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
            'lastmod' => 'datetime',
            'is_active' => 'boolean',
            'priority' => 'decimal:1',
        ];
    }
}
