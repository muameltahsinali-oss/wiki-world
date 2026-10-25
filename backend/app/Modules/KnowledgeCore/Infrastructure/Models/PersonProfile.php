<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonProfile extends Model
{
    protected $primaryKey = 'entity_id';

    public $incrementing = false;

    protected $fillable = [
        'entity_id',
        'kunya',
        'titles',
        'birth_date_hijri',
        'birth_date_gregorian',
        'death_date_hijri',
        'death_date_gregorian',
        'is_masum',
        'ahlulbayt_generation',
        'imam_order',
        'gender',
        'attributes',
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
            'titles' => 'array',
            'birth_date_gregorian' => 'date',
            'death_date_gregorian' => 'date',
            'is_masum' => 'boolean',
            'attributes' => 'array',
        ];
    }
}
