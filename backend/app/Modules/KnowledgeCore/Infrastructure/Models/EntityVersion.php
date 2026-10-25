<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntityVersion extends Model
{
    public const UPDATED_AT = null;

    protected static function booted(): void
    {
        static::updating(static function (EntityVersion $version): void {
            $immutableFields = ['snapshot', 'version_number', 'entity_id', 'change_summary', 'created_by_user_id'];

            foreach ($immutableFields as $field) {
                if ($version->isDirty($field)) {
                    throw new \RuntimeException('Entity version snapshots are immutable.');
                }
            }
        });
    }

    protected $fillable = [
        'entity_id',
        'version_number',
        'change_summary',
        'snapshot',
        'is_published_snapshot',
        'created_by_user_id',
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
            'snapshot' => 'array',
            'is_published_snapshot' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
