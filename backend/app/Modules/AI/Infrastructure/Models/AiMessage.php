<?php

declare(strict_types=1);

namespace App\Modules\AI\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiMessage extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'ai_messages';

    protected $fillable = [
        'uuid',
        'conversation_id',
        'role',
        'content',
    ];

    /**
     * @return BelongsTo<AiConversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }

    /**
     * @return HasMany<AiCitationLink, $this>
     */
    public function citations(): HasMany
    {
        return $this->hasMany(AiCitationLink::class, 'message_id');
    }

    /**
     * @return HasMany<AiResponseFlag, $this>
     */
    public function flags(): HasMany
    {
        return $this->hasMany(AiResponseFlag::class, 'message_id');
    }
}
