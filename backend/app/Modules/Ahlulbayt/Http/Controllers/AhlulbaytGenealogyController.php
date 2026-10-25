<?php

declare(strict_types=1);

namespace App\Modules\Ahlulbayt\Http\Controllers;

use App\Modules\Ahlulbayt\Application\Services\AhlulbaytService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AhlulbaytGenealogyController
{
    public function __construct(
        private readonly AhlulbaytService $ahlulbayt,
    ) {}

    public function __invoke(Request $request, string $locale, string $uuid): JsonResponse
    {
        $depth = min(5, max(1, (int) $request->input('depth', 3)));

        return ApiResponse::success($this->ahlulbayt->getGenealogy($uuid, $locale, $depth));
    }
}
