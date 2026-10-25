<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntityAlias extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'entity_id',
        'language_id',
        'text',
        'alias_type',
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
            'created_at' => 'datetime',
        ];
    }
}
