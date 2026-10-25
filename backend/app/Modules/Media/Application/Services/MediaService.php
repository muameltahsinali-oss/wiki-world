<?php

declare(strict_types=1);

namespace App\Modules\Media\Application\Services;

use App\Modules\Media\Infrastructure\Models\MediaFile;
use App\Modules\Media\Jobs\GenerateImageVariantsJob;
use App\Modules\Shared\Application\Contracts\StorageGatewayInterface;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class MediaService
{
    public function __construct(
        private readonly StorageGatewayInterface $storage,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function upload(User $user, UploadedFile $file, ?string $altText = null): array
    {
        $uuid = (string) Str::uuid7();
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $path = 'media/'.date('Y/m').'/'.$uuid.'.'.$extension;
        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            throw new FileException('Unable to read uploaded file.');
        }

        $disk = config('filesystems.default', 'local');

        $media = MediaFile::query()->create([
            'uuid' => $uuid,
            'media_type' => str_starts_with((string) $file->getMimeType(), 'image/') ? 'image' : 'file',
            'storage_disk' => $disk,
            'storage_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'alt_text' => $altText,
            'mime_type' => (string) $file->getMimeType(),
            'status' => 'processing',
            'file_size_bytes' => $file->getSize(),
            'checksum_sha256' => hash('sha256', $contents),
            'uploaded_by_user_id' => $user->id,
            'created_at' => now(),
        ]);

        if ($media->media_type === 'image') {
            GenerateImageVariantsJob::dispatch($media->id);
        } else {
            $media->update(['status' => 'ready']);
        }

        return $this->mapMedia($media->fresh('variants'));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{data: list<array<string, mixed>>, pagination: array<string, int>}
     */
    public function list(array $filters, int $page, int $perPage): array
    {
        $query = MediaFile::query()->with('variants')->orderByDesc('created_at');

        if (isset($filters['type']) && is_string($filters['type'])) {
            $query->where('media_type', $filters['type']);
        }

        if (isset($filters['status']) && is_string($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => collect($paginator->items())
                ->map(fn (MediaFile $media): array => $this->mapMedia($media))
                ->all(),
            'pagination' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getByUuid(string $uuid): array
    {
        $media = MediaFile::query()->with('variants')->where('uuid', $uuid)->firstOrFail();

        return $this->mapMedia($media);
    }

    /**
     * @return array<string, mixed>
     */
    public function updateMetadata(string $uuid, ?string $altText): array
    {
        if ($altText === null || trim($altText) === '') {
            throw new \InvalidArgumentException('alt_text is required.');
        }

        $media = MediaFile::query()->where('uuid', $uuid)->firstOrFail();
        $media->alt_text = $altText;
        $media->save();

        return $this->mapMedia($media->fresh('variants'));
    }

    public function attachToEntity(string $entityUuid, string $mediaUuid, string $role = 'inline'): void
    {
        $entityId = (int) DB::table('entities')->where('uuid', $entityUuid)->value('id');
        $mediaId = (int) MediaFile::query()->where('uuid', $mediaUuid)->value('id');

        if ($entityId === 0 || $mediaId === 0) {
            throw new \InvalidArgumentException('Entity or media not found.');
        }

        DB::table('entity_media')->updateOrInsert(
            ['entity_id' => $entityId, 'media_file_id' => $mediaId, 'role' => $role],
            ['sort_order' => 0],
        );
    }

    /**
     * @return array{url: string, expires_at: string}
     */
    public function signedUrl(string $uuid): array
    {
        $media = MediaFile::query()->where('uuid', $uuid)->firstOrFail();

        return [
            'url' => $this->resolveUrl($media->storage_path),
            'expires_at' => now()->addHour()->toIso8601String(),
        ];
    }

    private function resolveUrl(string $path): string
    {
        $cdn = config('wikiworld.media.cdn_url');

        if (is_string($cdn) && $cdn !== '') {
            return rtrim($cdn, '/').'/'.ltrim($path, '/');
        }

        return $this->storage->url($path);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapMedia(MediaFile $media): array
    {
        return [
            'uuid' => $media->uuid,
            'media_type' => $media->media_type,
            'status' => $media->status,
            'mime_type' => $media->mime_type,
            'original_filename' => $media->original_filename,
            'alt_text' => $media->alt_text,
            'file_size_bytes' => $media->file_size_bytes,
            'url' => $this->resolveUrl($media->storage_path),
            'variants' => $media->variants->map(fn ($variant): array => [
                'variant_type' => $variant->variant_type,
                'storage_path' => $variant->storage_path,
                'url' => $this->resolveUrl($variant->storage_path),
                'width' => $variant->width,
                'height' => $variant->height,
            ])->all(),
            'created_at' => $media->created_at?->toIso8601String(),
        ];
    }
}
