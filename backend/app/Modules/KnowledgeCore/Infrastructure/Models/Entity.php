<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'entity_type_id',
        'workflow_state',
        'module_flags',
        'is_indexable',
        'published_at',
        'published_version_id',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    /**
     * @return BelongsTo<EntityType, $this>
     */
    public function entityType(): BelongsTo
    {
        return $this->belongsTo(EntityType::class);
    }

    /**
     * @return HasOne<PersonProfile, $this>
     */
    public function personProfile(): HasOne
    {
        return $this->hasOne(PersonProfile::class, 'entity_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'module_flags' => 'array',
            'is_indexable' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
