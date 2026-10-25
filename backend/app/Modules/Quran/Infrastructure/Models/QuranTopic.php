<?php

declare(strict_types=1);

namespace App\Modules\Quran\Infrastructure\Models;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class QuranTopic extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'description',
        'entity_id',
    ];

    /**
     * @return BelongsTo<Entity, $this>
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * @return BelongsToMany<QuranVerse, $this>
     */
    public function verses(): BelongsToMany
    {
        return $this->belongsToMany(QuranVerse::class, 'quran_topic_verses', 'topic_id', 'verse_id');
    }
}
