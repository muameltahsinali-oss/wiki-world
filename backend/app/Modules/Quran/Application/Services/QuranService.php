<?php

declare(strict_types=1);

namespace App\Modules\Quran\Application\Services;

use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\Quran\Infrastructure\Models\QuranCrossReference;
use App\Modules\Quran\Infrastructure\Models\QuranSurah;
use App\Modules\Quran\Infrastructure\Models\QuranTopic;
use App\Modules\Quran\Infrastructure\Models\QuranVerse;
use App\Modules\Quran\Infrastructure\Models\TafsirProfile;
use App\Modules\Quran\Infrastructure\Models\TranslationProfile;
use App\Modules\Users\Infrastructure\Models\Language;
use Illuminate\Support\Facades\DB;

final class QuranService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listSurahs(string $locale): array
    {
        $languageId = Language::query()->where('code', $locale)->value('id');

        return QuranSurah::query()
            ->with(['entity'])
            ->orderBy('surah_number')
            ->get()
            ->map(function (QuranSurah $surah) use ($languageId, $locale): array {
                $localization = DB::table('entity_localizations')
                    ->where('entity_id', $surah->entity_id)
                    ->when($languageId !== null, static fn ($q) => $q->where('language_id', $languageId))
                    ->first();

                return [
                    'number' => $surah->surah_number,
                    'uuid' => $surah->entity->uuid,
                    'name_ar' => $localization->title ?? 'سورة '.$surah->surah_number,
                    'revelation_type' => $surah->revelation_type,
                    'verse_count' => $surah->verse_count,
                    'bismillah_prefix' => $surah->bismillah_prefix,
                    'locale' => $locale,
                ];
            })
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSurah(int $number, string $locale): ?array
    {
        $surah = QuranSurah::query()
            ->with('entity')
            ->where('surah_number', $number)
            ->first();

        if ($surah === null) {
            return null;
        }

        $languageId = Language::query()->where('code', $locale)->value('id');
        $localization = DB::table('entity_localizations')
            ->where('entity_id', $surah->entity_id)
            ->when($languageId !== null, static fn ($q) => $q->where('language_id', $languageId))
            ->first();

        $preview = $this->listSurahVerses($number, $locale);
        $versesPreview = array_slice($preview, 0, 3);

        return [
            'number' => $surah->surah_number,
            'uuid' => $surah->entity->uuid,
            'name_ar' => $localization->title ?? 'سورة '.$surah->surah_number,
            'revelation_type' => $surah->revelation_type,
            'verse_count' => $surah->verse_count,
            'bismillah_prefix' => $surah->bismillah_prefix,
            'verses_preview' => $versesPreview,
            'locale' => $locale,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listSurahVerses(int $surahNumber, string $locale): array
    {
        return QuranVerse::query()
            ->with('entity')
            ->where('surah_number', $surahNumber)
            ->orderBy('ayah_number')
            ->get()
            ->map(fn (QuranVerse $verse): array => $this->mapVerse($verse, $locale))
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getVerse(int $surah, int $ayah, string $locale): ?array
    {
        $verse = QuranVerse::query()
            ->with(['entity', 'crossReferences'])
            ->where('surah_number', $surah)
            ->where('ayah_number', $ayah)
            ->first();

        if ($verse === null) {
            return null;
        }

        $mapped = $this->mapVerse($verse, $locale);
        $mapped['cross_references'] = $this->getCrossReferences($surah, $ayah);

        return $mapped;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listTranslations(string $locale): array
    {
        $languageId = Language::query()->where('code', $locale)->value('id');

        return TranslationProfile::query()
            ->with(['entity', 'language'])
            ->when($languageId !== null, static fn ($q) => $q->where('language_id', $languageId))
            ->get()
            ->unique('source_entity_id')
            ->map(static function (TranslationProfile $profile): array {
                $source = Entity::query()->find($profile->source_entity_id);

                return [
                    'source_uuid' => $source?->uuid,
                    'language' => $profile->language->code,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listTafsir(int $surah, int $ayah, string $locale): array
    {
        $verseEntityId = QuranVerse::query()
            ->where('surah_number', $surah)
            ->where('ayah_number', $ayah)
            ->value('entity_id');

        if ($verseEntityId === null) {
            return [];
        }

        $languageId = Language::query()->where('code', $locale)->value('id');

        return TafsirProfile::query()
            ->where('verse_entity_id', $verseEntityId)
            ->when($languageId !== null, static fn ($q) => $q->where('language_id', $languageId))
            ->get()
            ->map(static function (TafsirProfile $profile): array {
                $source = Entity::query()->find($profile->source_entity_id);

                return [
                    'excerpt' => $profile->excerpt_text,
                    'source_uuid' => $source?->uuid,
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listTopics(string $locale): array
    {
        return QuranTopic::query()
            ->orderBy('title')
            ->get()
            ->map(static fn (QuranTopic $topic): array => [
                'slug' => $topic->slug,
                'title' => $topic->title,
                'description' => $topic->description,
                'verse_count' => $topic->verses()->count(),
            ])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getCrossReferences(int $surah, int $ayah): array
    {
        $verse = QuranVerse::query()
            ->where('surah_number', $surah)
            ->where('ayah_number', $ayah)
            ->first();

        if ($verse === null) {
            return [];
        }

        return QuranCrossReference::query()
            ->where('source_verse_id', $verse->id)
            ->get()
            ->map(static fn (QuranCrossReference $ref): array => [
                'surah' => $ref->target_surah_number,
                'ayah' => $ref->target_ayah_number,
                'relationship' => $ref->relationship,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapVerse(QuranVerse $verse, string $locale): array
    {
        return [
            'uuid' => $verse->entity->uuid,
            'surah' => $verse->surah_number,
            'ayah' => $verse->ayah_number,
            'reference' => $verse->surah_number.':'.$verse->ayah_number,
            'text_uthmani' => $verse->text_uthmani,
            'text_simple' => $verse->text_normalized,
            'juz' => $verse->juz_number,
            'hizb' => $verse->hizb_number,
            'locale' => $locale,
        ];
    }
}
