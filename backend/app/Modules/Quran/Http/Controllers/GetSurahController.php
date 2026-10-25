<?php

declare(strict_types=1);

namespace App\Modules\Quran\Http\Controllers;

use App\Modules\Quran\Application\Services\QuranService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class GetSurahController
{
    public function __construct(
        private readonly QuranService $quran,
    ) {}

    public function __invoke(string $locale, int $number): JsonResponse
    {
        $surah = $this->quran->getSurah($number, $locale);

        if ($surah === null) {
            throw new NotFoundHttpException;
        }

        return ApiResponse::success($surah);
    }
}
