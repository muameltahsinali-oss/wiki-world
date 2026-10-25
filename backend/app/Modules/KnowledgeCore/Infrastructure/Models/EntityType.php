<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntityType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'class',
        'profile_table',
        'primary_domain',
        'is_mvp',
        'is_active',
        'sort_order',
    ];

    /**
     * @return HasMany<Entity, $this>
     */
    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_mvp' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
