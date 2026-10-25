<?php

declare(strict_types=1);

namespace App\Modules\AI\Infrastructure\Models;

use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiResponseFlag extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'ai_response_flags';

    protected $fillable = [
        'message_id',
        'flagged_by_user_id',
        'comment',
        'status',
        'resolved_by_user_id',
        'resolved_at',
    ];

    /**
     * @return BelongsTo<AiMessage, $this>
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(AiMessage::class, 'message_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function flaggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'flagged_by_user_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }
}
