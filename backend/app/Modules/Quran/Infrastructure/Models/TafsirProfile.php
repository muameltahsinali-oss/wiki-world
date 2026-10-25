<?php

declare(strict_types=1);

namespace App\Modules\Quran\Infrastructure\Models;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\Users\Infrastructure\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TafsirProfile extends Model
{
    protected $primaryKey = 'entity_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'entity_id',
        'verse_entity_id',
        'source_entity_id',
        'excerpt_text',
        'language_id',
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
}
