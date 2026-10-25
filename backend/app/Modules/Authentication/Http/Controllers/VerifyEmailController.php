<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Http\Controllers;

use App\Models\User;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class VerifyEmailController
{
    public function __invoke(Request $request, string $id, string $hash): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return ApiResponse::error('INVALID_VERIFICATION_LINK', __('errors.invalid_verification_link'), 403);
        }

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(['message' => 'already_verified']);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return ApiResponse::success(['message' => 'verified']);
    }
}
