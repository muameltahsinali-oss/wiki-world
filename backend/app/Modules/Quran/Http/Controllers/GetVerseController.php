<?php

declare(strict_types=1);

namespace App\Modules\Quran\Http\Controllers;

use App\Modules\Quran\Application\Services\QuranService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class GetVerseController
{
    public function __construct(
        private readonly QuranService $quran,
    ) {}

    public function __invoke(string $locale, int $surah, int $ayah): JsonResponse
    {
        $verse = $this->quran->getVerse($surah, $ayah, $locale);

        if ($verse === null) {
            throw new NotFoundHttpException;
        }

        return ApiResponse::success($verse);
    }
}
