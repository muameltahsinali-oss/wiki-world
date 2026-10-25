<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Http\Middleware;

use App\Modules\Shared\Support\ApiResponse;
use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return ApiResponse::error('EMAIL_NOT_VERIFIED', __('errors.email_not_verified'), 403);
        }

        return $next($request);
    }
}
