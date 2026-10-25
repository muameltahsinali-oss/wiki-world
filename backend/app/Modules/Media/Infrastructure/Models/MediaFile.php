<?php

declare(strict_types=1);

namespace App\Modules\Media\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaFile extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'uuid',
        'media_type',
        'storage_disk',
        'storage_path',
        'original_filename',
        'alt_text',
        'mime_type',
        'status',
        'file_size_bytes',
        'checksum_sha256',
        'uploaded_by_user_id',
    ];

    /**
     * @return HasMany<MediaVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class);
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
