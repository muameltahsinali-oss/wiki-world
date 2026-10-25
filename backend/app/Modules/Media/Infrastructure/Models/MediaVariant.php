<?php

declare(strict_types=1);

namespace App\Modules\Media\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaVariant extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'media_file_id',
        'variant_type',
        'storage_path',
        'width',
        'height',
        'file_size_bytes',
    ];

    /**
     * @return BelongsTo<MediaFile, $this>
     */
    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }
}
