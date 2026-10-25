<?php

declare(strict_types=1);

namespace App\Modules\Quran\Http\Controllers;

use App\Modules\Quran\Application\Services\QuranService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ListSurahVersesController
{
    public function __construct(
        private readonly QuranService $quran,
    ) {}

    public function __invoke(string $locale, int $number): JsonResponse
    {
        if ($this->quran->getSurah($number, $locale) === null) {
            throw new NotFoundHttpException;
        }

        return ApiResponse::success($this->quran->listSurahVerses($number, $locale));
    }
}
