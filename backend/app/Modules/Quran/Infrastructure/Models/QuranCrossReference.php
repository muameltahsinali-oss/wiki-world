<?php

declare(strict_types=1);

namespace App\Modules\Quran\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuranCrossReference extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'source_verse_id',
        'target_surah_number',
        'target_ayah_number',
        'relationship',
    ];

    /**
     * @return BelongsTo<QuranVerse, $this>
     */
    public function sourceVerse(): BelongsTo
    {
        return $this->belongsTo(QuranVerse::class, 'source_verse_id');
    }
}
