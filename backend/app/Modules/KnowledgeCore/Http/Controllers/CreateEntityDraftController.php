<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\KnowledgeCore\Application\Actions\CreateEntityDraftAction;
use App\Modules\KnowledgeCore\Application\DTOs\CreateEntityDraftData;
use App\Modules\KnowledgeCore\Http\Requests\CreateEntityDraftRequest;
use App\Modules\KnowledgeCore\Http\Resources\EditorialEntityResource;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;

final class CreateEntityDraftController
{
    public function __construct(
        private readonly CreateEntityDraftAction $createEntityDraft,
    ) {}

    public function __invoke(CreateEntityDraftRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $draft = $this->createEntityDraft->execute($user, new CreateEntityDraftData(
            entityTypeCode: $request->string('entity_type')->toString(),
            domain: $request->string('domain')->toString(),
            locale: $request->string('locale')->toString(),
            title: $request->string('title')->toString(),
            slug: $request->has('slug') ? $request->string('slug')->toString() : null,
            summary: $request->has('summary') ? $request->string('summary')->toString() : null,
            createdByUserId: $user->id,
        ));

        return ApiResponse::success(
            EditorialEntityResource::fromDraft($draft),
            status: 201,
        );
    }
}
