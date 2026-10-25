<?php

declare(strict_types=1);

namespace App\Modules\Quran\Infrastructure\Models;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuranCorpus extends Model
{
    public $timestamps = false;

    protected $table = 'quran_corpus';

    protected $fillable = [
        'entity_id',
        'script_edition',
        'total_surahs',
        'total_verses',
        'seeded_at',
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
            'seeded_at' => 'datetime',
        ];
    }
}
