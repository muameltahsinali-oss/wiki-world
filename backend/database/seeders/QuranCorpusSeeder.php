<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\Quran\Domain\QuranVerseCounts;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class QuranCorpusSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('quran_corpus')->exists()) {
            return;
        }

        $now = now();
        $arabicLanguageId = (int) DB::table('languages')->where('code', 'ar')->value('id');
        $typeIds = DB::table('entity_types')
            ->whereIn('code', ['quran', 'surah', 'verse'])
            ->pluck('id', 'code');

        $corpusEntityId = $this->insertEntity(
            (string) Str::uuid(),
            (int) $typeIds['quran'],
            ['domain' => 'quran'],
            $now,
        );

        DB::table('entity_localizations')->insert([
            'entity_id' => $corpusEntityId,
            'language_id' => $arabicLanguageId,
            'title' => 'القرآن الكريم',
            'slug' => 'quran',
            'summary' => 'المصحف الشريف',
            'workflow_state' => WorkflowState::Published->value,
            'translation_status' => 'original',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('quran_corpus')->insert([
            'entity_id' => $corpusEntityId,
            'script_edition' => 'uthmani-hafs',
            'total_surahs' => 114,
            'total_verses' => QuranVerseCounts::totalVerses(),
            'seeded_at' => $now,
        ]);

        foreach (QuranVerseCounts::VERSES_PER_SURAH as $index => $verseCount) {
            $surahNumber = $index + 1;
            $surahEntityId = $this->insertEntity(
                (string) Str::uuid(),
                (int) $typeIds['surah'],
                ['domain' => 'quran', 'surah_number' => $surahNumber],
                $now,
            );

            DB::table('entity_localizations')->insert([
                'entity_id' => $surahEntityId,
                'language_id' => $arabicLanguageId,
                'title' => 'سورة '.$surahNumber,
                'slug' => 'surah-'.$surahNumber,
                'summary' => null,
                'workflow_state' => WorkflowState::Published->value,
                'translation_status' => 'original',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('quran_surahs')->insert([
                'surah_number' => $surahNumber,
                'entity_id' => $surahEntityId,
                'verse_count' => $verseCount,
                'revelation_type' => QuranVerseCounts::revelationType($surahNumber),
                'bismillah_prefix' => $surahNumber !== 9,
            ]);

            $entityRows = [];

            for ($ayah = 1; $ayah <= $verseCount; $ayah++) {
                $entityRows[] = [
                    'uuid' => (string) Str::uuid(),
                    'entity_type_id' => (int) $typeIds['verse'],
                    'workflow_state' => WorkflowState::Published->value,
                    'module_flags' => json_encode([
                        'domain' => 'quran',
                        'surah_number' => $surahNumber,
                        'ayah_number' => $ayah,
                    ]),
                    'is_indexable' => true,
                    'published_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $verseEntityIds = [];

            foreach (array_chunk($entityRows, 200) as $chunk) {
                $startId = (int) DB::table('entities')->max('id') + 1;
                DB::table('entities')->insert($chunk);
                $endId = (int) DB::table('entities')->max('id');

                for ($id = $startId; $id <= $endId; $id++) {
                    $verseEntityIds[] = $id;
                }
            }

            $localizationRows = [];
            $verseRows = [];

            foreach ($verseEntityIds as $offset => $verseEntityId) {
                $ayah = $offset + 1;
                $placeholder = '﴿'.$surahNumber.':'.$ayah.'﴾';

                $localizationRows[] = [
                    'entity_id' => $verseEntityId,
                    'language_id' => $arabicLanguageId,
                    'title' => $surahNumber.':'.$ayah,
                    'slug' => $surahNumber.'-'.$ayah,
                    'summary' => null,
                    'workflow_state' => WorkflowState::Published->value,
                    'translation_status' => 'original',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $verseRows[] = [
                    'entity_id' => $verseEntityId,
                    'surah_number' => $surahNumber,
                    'ayah_number' => $ayah,
                    'text_uthmani' => $placeholder,
                    'text_normalized' => $placeholder,
                    'juz_number' => null,
                    'hizb_number' => null,
                ];
            }

            foreach (array_chunk($localizationRows, 200) as $chunk) {
                DB::table('entity_localizations')->insert($chunk);
            }

            foreach (array_chunk($verseRows, 200) as $chunk) {
                DB::table('quran_verses')->insert($chunk);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $moduleFlags
     */
    private function insertEntity(string $uuid, int $typeId, array $moduleFlags, Carbon $now): int
    {
        return (int) DB::table('entities')->insertGetId([
            'uuid' => $uuid,
            'entity_type_id' => $typeId,
            'workflow_state' => WorkflowState::Published->value,
            'module_flags' => json_encode($moduleFlags),
            'is_indexable' => true,
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
