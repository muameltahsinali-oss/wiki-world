<?php

declare(strict_types=1);

namespace App\Modules\SEO\Infrastructure\Models;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\Users\Infrastructure\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UrlRedirect extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'url_redirects';

    protected $fillable = [
        'language_id',
        'from_path',
        'to_path',
        'entity_id',
        'redirect_type',
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
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
