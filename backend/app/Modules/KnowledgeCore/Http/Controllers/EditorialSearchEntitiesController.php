<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\KnowledgeCore\Application\Services\EditorialInsightsService;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EditorialSearchEntitiesController
{
    public function __construct(
        private readonly EditorialInsightsService $insights,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $locale = $request->string('locale', config('wikiworld.default_locale', 'ar'))->toString();
        $query = (string) $request->input('q', '');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(1, (int) $request->input('per_page', 20)));

        $result = $this->insights->searchEntities($user, $locale, $query, $page, $perPage);

        return ApiResponse::success($result['data'], meta: ['pagination' => $result['pagination']]);
    }
}
