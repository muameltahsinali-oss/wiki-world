<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Controllers;

use App\Modules\Settings\Application\Services\FeatureToggleService;
use App\Modules\Settings\Application\Services\SettingsService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class GetPublicSiteConfigController
{
    public function __construct(
        private readonly FeatureToggleService $toggles,
        private readonly SettingsService $settings,
    ) {}

    public function __invoke(): JsonResponse
    {
        return ApiResponse::success([
            'maintenance_mode' => (bool) $this->settings->get('maintenance_mode', false)
                || $this->toggles->isEnabled('maintenance_mode'),
            'registration_enabled' => $this->toggles->isEnabled('registration_enabled'),
            'search_enabled' => $this->toggles->isEnabled('search_enabled'),
            'ai_enabled' => $this->toggles->isEnabled('ai_enabled'),
        ]);
    }
}
