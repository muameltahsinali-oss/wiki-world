<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\KnowledgeCore\Application\Actions\SaveEntityDraftAction;
use App\Modules\KnowledgeCore\Application\DTOs\SaveEntityDraftData;
use App\Modules\KnowledgeCore\Http\Requests\SaveEntityDraftRequest;
use App\Modules\KnowledgeCore\Http\Resources\EditorialEntityResource;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;

final class SaveEntityDraftController
{
    public function __construct(
        private readonly SaveEntityDraftAction $saveEntityDraft,
    ) {}

    public function __invoke(SaveEntityDraftRequest $request, string $uuid): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $view = $this->saveEntityDraft->execute($user, new SaveEntityDraftData(
            uuid: $uuid,
            locale: $request->string('locale')->toString(),
            title: $request->has('title') ? $request->string('title')->toString() : null,
            slug: $request->has('slug') ? $request->string('slug')->toString() : null,
            summary: $request->has('summary') ? $request->string('summary')->toString() : null,
            profile: $request->has('profile') ? $request->array('profile') : null,
            blocks: $request->has('blocks') ? $request->array('blocks') : null,
        ));

        return ApiResponse::success(EditorialEntityResource::fromView($view));
    }
}
