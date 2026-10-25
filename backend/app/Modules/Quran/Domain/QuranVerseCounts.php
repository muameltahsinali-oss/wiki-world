<?php

declare(strict_types=1);

namespace App\Modules\Quran\Domain;

final class QuranVerseCounts
{
    /** @var list<int> */
    public const VERSES_PER_SURAH = [
        7, 286, 200, 176, 120, 165, 206, 75, 129, 109,
        123, 111, 43, 52, 99, 128, 111, 110, 98, 135,
        112, 78, 118, 64, 77, 227, 93, 88, 69, 60,
        34, 30, 73, 54, 45, 83, 182, 88, 75, 85,
        54, 53, 89, 59, 37, 35, 38, 29, 18, 45,
        60, 49, 62, 55, 78, 96, 29, 22, 24, 13,
        14, 11, 11, 18, 12, 12, 30, 52, 52, 44,
        28, 28, 20, 56, 40, 31, 50, 40, 46, 42,
        29, 19, 36, 25, 22, 17, 19, 26, 30, 20,
        15, 21, 11, 8, 8, 19, 5, 8, 8, 11,
        11, 8, 3, 9, 5, 4, 7, 3, 6, 3,
        5, 4, 5, 6,
    ];

    /** @var list<int> */
    public const MEDINAN_SURAH_NUMBERS = [
        2, 3, 4, 5, 8, 9, 13, 16, 22, 24, 33, 47, 48, 49, 55, 57, 58, 59, 60, 61,
        62, 63, 64, 65, 66, 76, 98, 99, 110, 111,
    ];

    public static function totalVerses(): int
    {
        return array_sum(self::VERSES_PER_SURAH);
    }

    public static function revelationType(int $surahNumber): string
    {
        return in_array($surahNumber, self::MEDINAN_SURAH_NUMBERS, true) ? 'medinan' : 'meccan';
    }
}
