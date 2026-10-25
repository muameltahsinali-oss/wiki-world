<?php

declare(strict_types=1);

namespace App\Modules\SEO\Application\Services;

use App\Modules\SEO\Infrastructure\Models\UrlRedirect;
use App\Modules\Users\Infrastructure\Models\Language;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class RedirectService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function list(): array
    {
        return UrlRedirect::query()
            ->orderByDesc('id')
            ->get()
            ->map(static fn (UrlRedirect $r): array => [
                'id' => $r->id,
                'from_path' => $r->from_path,
                'to_path' => $r->to_path,
                'redirect_type' => $r->redirect_type,
                'is_active' => $r->is_active,
                'language_id' => $r->language_id,
            ])
            ->all();
    }

    /**
     * @param  array{from_path: string, to_path?: ?string, status?: int, locale?: string, entity_id?: ?int}  $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        $locale = $data['locale'] ?? config('wikiworld.default_locale', 'ar');
        $languageId = (int) Language::query()->where('code', $locale)->value('id');

        $redirect = UrlRedirect::query()->create([
            'language_id' => $languageId,
            'from_path' => $data['from_path'],
            'to_path' => $data['to_path'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
            'redirect_type' => (int) ($data['status'] ?? 301),
            'is_active' => true,
        ]);

        return [
            'id' => $redirect->id,
            'from_path' => $redirect->from_path,
            'to_path' => $redirect->to_path,
            'redirect_type' => $redirect->redirect_type,
            'is_active' => $redirect->is_active,
        ];
    }

    public function delete(int $id): void
    {
        $redirect = UrlRedirect::query()->find($id);

        if ($redirect === null) {
            throw (new ModelNotFoundException)->setModel(UrlRedirect::class, [$id]);
        }

        $redirect->delete();
    }
}
