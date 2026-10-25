<?php

declare(strict_types=1);

namespace App\Modules\RolesPermissions\Http\Middleware;

use App\Modules\RolesPermissions\Application\Contracts\AuthorizationServiceInterface;
use App\Modules\RolesPermissions\Domain\Enums\Permission;
use App\Modules\Shared\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureEditorialAccess
{
    public function __construct(
        private readonly AuthorizationServiceInterface $authorization,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error('UNAUTHENTICATED', __('errors.unauthenticated'), 401);
        }

        if (! $this->authorization->userHasPermission($user, Permission::EntityViewDraft->value)) {
            return ApiResponse::error('FORBIDDEN', __('errors.forbidden'), 403);
        }

        return $next($request);
    }
}
