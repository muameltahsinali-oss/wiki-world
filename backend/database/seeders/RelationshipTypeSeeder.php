<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class RelationshipTypeSeeder extends Seeder
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function definitions(): array
    {
        return [
            ['code' => 'participated_in', 'label_en' => 'participated in', 'label_ar' => 'شارك في', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'occurred_at', 'label_en' => 'occurred at', 'label_ar' => 'وقع في', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'related_to', 'label_en' => 'related to', 'label_ar' => 'مرتبط بـ', 'inverse_code' => null, 'is_symmetric' => true],
            ['code' => 'born_at', 'label_en' => 'born at', 'label_ar' => 'ولد في', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'died_at', 'label_en' => 'died at', 'label_ar' => 'توفي في', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'authored', 'label_en' => 'authored', 'label_ar' => 'ألف', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'mentions', 'label_en' => 'mentions', 'label_ar' => 'يذكر', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'references', 'label_en' => 'references', 'label_ar' => 'يستشهد بـ', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'explained_by', 'label_en' => 'explained by', 'label_ar' => 'يُفسَّر بـ', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'translated_by', 'label_en' => 'translated by', 'label_ar' => 'تُرجم بـ', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'belongs_to', 'label_en' => 'belongs to', 'label_ar' => 'ينتمي إلى', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'defined_by', 'label_en' => 'defined by', 'label_ar' => 'يُعرَّف بـ', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'illustrated_by', 'label_en' => 'illustrated by', 'label_ar' => 'يُوضَّح بـ', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'tagged_with', 'label_en' => 'tagged with', 'label_ar' => 'مُوسوم بـ', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'member_of', 'label_en' => 'member of', 'label_ar' => 'عضو في', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'parent_of', 'label_en' => 'parent of', 'label_ar' => 'أصل لـ', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'supported_by', 'label_en' => 'supported by', 'label_ar' => 'مدعوم بـ', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'published_by', 'label_en' => 'published by', 'label_ar' => 'نشر بواسطة', 'inverse_code' => null, 'is_symmetric' => false],
            ['code' => 'part_of', 'label_en' => 'part of', 'label_ar' => 'جزء من', 'inverse_code' => null, 'is_symmetric' => false],
        ];
    }

    public function run(): void
    {
        $rows = array_map(
            static fn (array $definition): array => [
                ...$definition,
                'is_active' => true,
            ],
            self::definitions(),
        );

        DB::table('relationship_types')->upsert(
            $rows,
            ['code'],
            ['label_en', 'label_ar', 'inverse_code', 'is_symmetric', 'is_active'],
        );
    }
}
