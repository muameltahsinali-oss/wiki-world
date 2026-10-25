<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EntityRelationship extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'relationship_type_id',
        'source_entity_id',
        'target_entity_id',
        'metadata',
        'sort_order',
        'valid_from',
        'valid_to',
        'created_by_user_id',
    ];

    /**
     * @return BelongsTo<Entity, $this>
     */
    public function sourceEntity(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'source_entity_id');
    }

    /**
     * @return BelongsTo<Entity, $this>
     */
    public function targetEntity(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'target_entity_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'valid_from' => 'datetime',
            'valid_to' => 'datetime',
        ];
    }
}
