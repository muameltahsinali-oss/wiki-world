<?php

declare(strict_types=1);

namespace App\Modules\Media\Jobs;

use App\Modules\Media\Infrastructure\Models\MediaFile;
use App\Modules\Media\Infrastructure\Models\MediaVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class GenerateImageVariantsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $mediaFileId,
    ) {}

    public function handle(): void
    {
        $media = MediaFile::query()->find($this->mediaFileId);

        if ($media === null) {
            return;
        }

        foreach (['thumbnail', 'medium', 'large'] as $variant) {
            MediaVariant::query()->create([
                'media_file_id' => $media->id,
                'variant_type' => $variant,
                'storage_path' => $media->storage_path,
                'width' => match ($variant) {
                    'thumbnail' => 150,
                    'medium' => 600,
                    default => 1200,
                },
                'height' => null,
                'file_size_bytes' => $media->file_size_bytes,
            ]);
        }

        $media->update(['status' => 'ready']);
    }
}
