<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceProfile extends Model
{
    protected $primaryKey = 'entity_id';

    public $incrementing = false;

    protected $fillable = [
        'entity_id',
        'place_type',
        'latitude',
        'longitude',
        'modern_country_code',
        'historical_names',
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
            'historical_names' => 'array',
            'attributes' => 'array',
        ];
    }
}
