<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Http\Controllers;

use App\Modules\Authentication\Application\Actions\LogoutUserAction;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class LogoutController
{
    public function __construct(
        private readonly LogoutUserAction $logoutUser,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->logoutUser->execute($request->bearerToken());

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return ApiResponse::noContent();
    }
}
