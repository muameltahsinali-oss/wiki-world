<?php

declare(strict_types=1);

namespace App\Modules\Quran\Infrastructure\Models;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuranVerse extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'entity_id',
        'surah_number',
        'ayah_number',
        'text_uthmani',
        'text_normalized',
        'juz_number',
        'hizb_number',
    ];

    /**
     * @return BelongsTo<Entity, $this>
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * @return BelongsTo<QuranSurah, $this>
     */
    public function surah(): BelongsTo
    {
        return $this->belongsTo(QuranSurah::class, 'surah_number', 'surah_number');
    }

    /**
     * @return HasMany<QuranCrossReference, $this>
     */
    public function crossReferences(): HasMany
    {
        return $this->hasMany(QuranCrossReference::class, 'source_verse_id');
    }
}
