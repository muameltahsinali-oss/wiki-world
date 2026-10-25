<?php

declare(strict_types=1);

namespace App\Modules\Quran\Http\Controllers;

use App\Modules\Quran\Application\Services\QuranService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class ListVerseTafsirController
{
    public function __construct(
        private readonly QuranService $quran,
    ) {}

    public function __invoke(string $locale, int $surah, int $ayah): JsonResponse
    {
        return ApiResponse::success($this->quran->listTafsir($surah, $ayah, $locale));
    }
}
