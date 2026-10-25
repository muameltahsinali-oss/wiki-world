<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\Services\EntityAccessService;
use App\Modules\KnowledgeCore\Application\Services\EntityQueryService;
use App\Modules\KnowledgeCore\Domain\Exceptions\DomainScopeDeniedException;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetEntityPreviewController
{
    public function __construct(
        private readonly KnowledgeEntityRepositoryInterface $entities,
        private readonly EntityAccessService $access,
        private readonly EntityQueryService $queries,
    ) {}

    public function __invoke(Request $request, string $uuid): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $entity = $this->entities->findByUuid($uuid);

        if ($entity === null || ! $this->access->canViewDraft($user, $entity)) {
            throw new DomainScopeDeniedException;
        }

        $locale = $request->string('locale', config('wikiworld.default_locale', 'ar'))->toString();
        $view = $this->queries->loadPreviewView($uuid, $locale);

        return ApiResponse::success($view);
    }
}
