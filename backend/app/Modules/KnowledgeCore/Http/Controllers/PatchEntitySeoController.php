<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\Services\EntityAccessService;
use App\Modules\KnowledgeCore\Application\Services\EntitySeoService;
use App\Modules\KnowledgeCore\Domain\Exceptions\DomainScopeDeniedException;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PatchEntitySeoController
{
    public function __construct(
        private readonly KnowledgeEntityRepositoryInterface $entities,
        private readonly EntityAccessService $access,
        private readonly EntitySeoService $seo,
    ) {}

    public function __invoke(Request $request, string $uuid): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $entity = $this->entities->findByUuid($uuid);

        if ($entity === null || ! $this->access->canUpdate($user, $entity)) {
            throw new DomainScopeDeniedException;
        }

        $locale = $request->string('locale', config('wikiworld.default_locale', 'ar'))->toString();

        return ApiResponse::success($this->seo->update($entity->id(), $locale, [
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
        ]));
    }
}
