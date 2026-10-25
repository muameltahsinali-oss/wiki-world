<?php

declare(strict_types=1);

namespace App\Modules\Quran\Infrastructure\Models;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuranSurah extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'surah_number';

    protected $keyType = 'int';

    protected $fillable = [
        'surah_number',
        'entity_id',
        'verse_count',
        'revelation_type',
        'bismillah_prefix',
    ];

    /**
     * @return BelongsTo<Entity, $this>
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * @return HasMany<QuranVerse, $this>
     */
    public function verses(): HasMany
    {
        return $this->hasMany(QuranVerse::class, 'surah_number', 'surah_number');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bismillah_prefix' => 'boolean',
        ];
    }
}
