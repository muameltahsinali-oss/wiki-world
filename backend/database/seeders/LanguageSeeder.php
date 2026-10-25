<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class LanguageSeeder extends Seeder
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function definitions(): array
    {
        return [
            [
                'code' => 'ar',
                'name_en' => 'Arabic',
                'name_native' => 'العربية',
                'direction' => 'rtl',
                'numeral_system' => 'arabic',
                'is_primary' => true,
                'is_active' => true,
                'fallback_language_id' => null,
                'sort_order' => 1,
            ],
            [
                'code' => 'en',
                'name_en' => 'English',
                'name_native' => 'English',
                'direction' => 'ltr',
                'numeral_system' => 'western',
                'is_primary' => false,
                'is_active' => true,
                'fallback_language_id' => null,
                'sort_order' => 2,
            ],
        ];
    }

    public function run(): void
    {
        $definitions = self::definitions();

        DB::table('languages')->upsert(
            $definitions,
            ['code'],
            [
                'name_en',
                'name_native',
                'direction',
                'numeral_system',
                'is_primary',
                'is_active',
                'fallback_language_id',
                'sort_order',
            ],
        );

        $arabicId = DB::table('languages')->where('code', 'ar')->value('id');

        DB::table('languages')
            ->where('code', 'en')
            ->update(['fallback_language_id' => $arabicId]);
    }
}
