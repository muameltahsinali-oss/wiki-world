<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventProfile extends Model
{
    protected $primaryKey = 'entity_id';

    public $incrementing = false;

    protected $fillable = [
        'entity_id',
        'start_date_hijri',
        'end_date_hijri',
        'start_date_gregorian',
        'end_date_gregorian',
        'date_precision',
        'era_code',
        'significance',
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
            'start_date_gregorian' => 'date',
            'end_date_gregorian' => 'date',
            'attributes' => 'array',
        ];
    }
}
