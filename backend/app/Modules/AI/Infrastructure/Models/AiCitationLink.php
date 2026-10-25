<?php

declare(strict_types=1);

namespace App\Modules\AI\Infrastructure\Models;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiCitationLink extends Model
{
    public $timestamps = false;

    protected $table = 'ai_citation_links';

    protected $fillable = [
        'message_id',
        'reference_id',
        'entity_id',
        'segment_start',
        'segment_end',
        'sort_order',
    ];

    /**
     * @return BelongsTo<AiMessage, $this>
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(AiMessage::class, 'message_id');
    }

    /**
     * @return BelongsTo<Entity, $this>
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }
}
