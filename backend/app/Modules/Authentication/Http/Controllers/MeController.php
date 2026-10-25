<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Http\Controllers;

use App\Modules\Authentication\Http\Requests\UpdateMeRequest;
use App\Modules\Authentication\Http\Resources\UserProfileResource;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Application\Contracts\UserRepositoryInterface;
use App\Modules\Users\Application\DTOs\UpdateUserData;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MeController
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->load(['preferredLanguage', 'roles']);

        return ApiResponse::success(UserProfileResource::fromModel($user));
    }

    public function update(UpdateMeRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $updated = $this->users->update($user->uuid, new UpdateUserData(
            displayName: $request->has('display_name') ? $request->string('display_name')->toString() : null,
            preferredLocale: $request->has('preferred_locale') ? $request->string('preferred_locale')->toString() : null,
        ));

        $model = User::query()
            ->with(['preferredLanguage', 'roles'])
            ->where('uuid', $updated->uuid)
            ->firstOrFail();

        return ApiResponse::success(UserProfileResource::fromModel($model));
    }

    public function destroy(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->tokens()->delete();
        $user->forceFill([
            'email' => 'deleted+'.$user->uuid.'@anonymous.wikiworld',
            'display_name' => 'Deleted User',
            'name' => 'Deleted User',
            'is_active' => false,
            'remember_token' => null,
        ])->save();

        $this->users->delete($user->uuid);

        return ApiResponse::noContent();
    }
}
