<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Controllers\Admin;

use App\Modules\Audit\Application\Contracts\AuditLoggerInterface;
use App\Modules\Settings\Application\Services\FeatureToggleService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PatchFeatureTogglesController
{
    public function __construct(
        private readonly FeatureToggleService $toggles,
        private readonly AuditLoggerInterface $audit,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'toggles' => ['required', 'array'],
            'toggles.*' => ['boolean'],
        ]);

        $updated = $this->toggles->updateMany($validated['toggles']);
        $this->audit->log('feature_toggles.update', 'feature_toggles', null, $validated['toggles'], $request->user()?->id);

        return ApiResponse::success($updated);
    }
}
