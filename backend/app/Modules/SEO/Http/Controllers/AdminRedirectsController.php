<?php

declare(strict_types=1);

namespace App\Modules\SEO\Http\Controllers;

use App\Modules\SEO\Application\Services\RedirectService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminRedirectsController
{
    public function __construct(
        private readonly RedirectService $redirects,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success($this->redirects->list());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_path' => ['required', 'string', 'max:1024'],
            'to_path' => ['nullable', 'string', 'max:1024'],
            'status' => ['nullable', 'integer', 'in:301,302'],
            'locale' => ['nullable', 'string', 'in:ar,en'],
            'entity_id' => ['nullable', 'integer'],
        ]);

        return ApiResponse::success($this->redirects->create($validated), status: 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->redirects->delete($id);

        return ApiResponse::noContent();
    }
}
