<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class EntityTypeSeeder extends Seeder
{
    /**
     * Ontology registry per Knowledge Domain Blueprint Appendix A
     * and Database Design §5.1.1.
     *
     * @return list<array<string, mixed>>
     */
    public static function definitions(): array
    {
        return [
            ['code' => 'person', 'class' => 'canonical', 'profile_table' => 'person_profiles', 'primary_domain' => 'ahlulbayt', 'is_mvp' => true, 'sort_order' => 1],
            ['code' => 'event', 'class' => 'canonical', 'profile_table' => 'event_profiles', 'primary_domain' => 'general', 'is_mvp' => true, 'sort_order' => 2],
            ['code' => 'place', 'class' => 'canonical', 'profile_table' => 'place_profiles', 'primary_domain' => 'general', 'is_mvp' => true, 'sort_order' => 3],
            ['code' => 'concept', 'class' => 'canonical', 'profile_table' => 'concept_profiles', 'primary_domain' => 'general', 'is_mvp' => true, 'sort_order' => 4],
            ['code' => 'source', 'class' => 'bibliographic', 'profile_table' => 'source_profiles', 'primary_domain' => 'general', 'is_mvp' => true, 'sort_order' => 5],
            ['code' => 'book', 'class' => 'canonical', 'profile_table' => 'book_profiles', 'primary_domain' => 'general', 'is_mvp' => true, 'sort_order' => 6],
            ['code' => 'article', 'class' => 'composed', 'profile_table' => null, 'primary_domain' => 'general', 'is_mvp' => true, 'sort_order' => 7],
            ['code' => 'media', 'class' => 'platform', 'profile_table' => 'media_files', 'primary_domain' => 'general', 'is_mvp' => true, 'sort_order' => 8],
            ['code' => 'quran', 'class' => 'canonical', 'profile_table' => 'quran_corpus', 'primary_domain' => 'quran', 'is_mvp' => true, 'sort_order' => 9],
            ['code' => 'surah', 'class' => 'canonical', 'profile_table' => 'quran_surahs', 'primary_domain' => 'quran', 'is_mvp' => true, 'sort_order' => 10],
            ['code' => 'verse', 'class' => 'canonical', 'profile_table' => 'quran_verses', 'primary_domain' => 'quran', 'is_mvp' => true, 'sort_order' => 11],
            ['code' => 'translation', 'class' => 'composed', 'profile_table' => 'translation_profiles', 'primary_domain' => 'quran', 'is_mvp' => true, 'sort_order' => 12],
            ['code' => 'tafsir', 'class' => 'composed', 'profile_table' => 'tafsir_profiles', 'primary_domain' => 'quran', 'is_mvp' => true, 'sort_order' => 13],
            ['code' => 'quote', 'class' => 'composed', 'profile_table' => 'quote_profiles', 'primary_domain' => 'general', 'is_mvp' => false, 'sort_order' => 14],
            ['code' => 'reference', 'class' => 'bibliographic', 'profile_table' => null, 'primary_domain' => 'general', 'is_mvp' => true, 'sort_order' => 15],
            ['code' => 'organization', 'class' => 'canonical', 'profile_table' => 'organization_profiles', 'primary_domain' => 'general', 'is_mvp' => false, 'sort_order' => 16],
            ['code' => 'category', 'class' => 'taxonomic', 'profile_table' => 'categories', 'primary_domain' => 'general', 'is_mvp' => true, 'sort_order' => 17],
            ['code' => 'tag', 'class' => 'taxonomic', 'profile_table' => 'tags', 'primary_domain' => 'general', 'is_mvp' => true, 'sort_order' => 18],
            ['code' => 'collection', 'class' => 'taxonomic', 'profile_table' => 'collections', 'primary_domain' => 'general', 'is_mvp' => true, 'sort_order' => 19],
            ['code' => 'topic', 'class' => 'taxonomic', 'profile_table' => 'topics', 'primary_domain' => 'general', 'is_mvp' => true, 'sort_order' => 20],
            ['code' => 'series', 'class' => 'taxonomic', 'profile_table' => 'series', 'primary_domain' => 'general', 'is_mvp' => false, 'sort_order' => 21],
            ['code' => 'language', 'class' => 'platform', 'profile_table' => 'languages', 'primary_domain' => 'general', 'is_mvp' => true, 'sort_order' => 22],
            ['code' => 'user', 'class' => 'platform', 'profile_table' => null, 'primary_domain' => 'general', 'is_mvp' => true, 'sort_order' => 23],
            ['code' => 'role', 'class' => 'platform', 'profile_table' => null, 'primary_domain' => 'general', 'is_mvp' => true, 'sort_order' => 24],
            ['code' => 'permission', 'class' => 'platform', 'profile_table' => null, 'primary_domain' => 'general', 'is_mvp' => true, 'sort_order' => 25],
            ['code' => 'hadith', 'class' => 'canonical', 'profile_table' => 'hadith_profiles', 'primary_domain' => 'general', 'is_mvp' => false, 'sort_order' => 26],
            ['code' => 'narrator', 'class' => 'canonical', 'profile_table' => 'narrator_profiles', 'primary_domain' => 'general', 'is_mvp' => false, 'sort_order' => 27],
            ['code' => 'manuscript', 'class' => 'canonical', 'profile_table' => 'manuscript_profiles', 'primary_domain' => 'general', 'is_mvp' => false, 'sort_order' => 28],
        ];
    }

    public function run(): void
    {
        $now = now();

        $rows = array_map(
            static fn (array $definition): array => [
                ...$definition,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            self::definitions(),
        );

        DB::table('entity_types')->upsert(
            $rows,
            ['code'],
            ['class', 'profile_table', 'primary_domain', 'is_mvp', 'is_active', 'sort_order', 'updated_at'],
        );
    }
}
