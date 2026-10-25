<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Middleware;

use App\Modules\Settings\Application\Services\FeatureToggleService;
use App\Modules\Shared\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureFeatureEnabled
{
    public function __construct(
        private readonly FeatureToggleService $toggles,
    ) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (! $this->toggles->isEnabled($feature)) {
            return ApiResponse::error(
                'FEATURE_DISABLED',
                __('errors.feature_disabled', ['feature' => $feature]),
                503,
            );
        }

        return $next($request);
    }
}
