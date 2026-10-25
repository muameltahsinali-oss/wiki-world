<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Controllers\Admin;

use App\Modules\Settings\Application\Services\FeatureToggleService;
use App\Modules\Settings\Application\Services\SettingsService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetAdminSettingsController
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly FeatureToggleService $toggles,
    ) {}

    public function __invoke(): JsonResponse
    {
        return ApiResponse::success([
            'maintenance_mode' => (bool) $this->settings->get('maintenance_mode', false),
            'site_name' => $this->settings->get('site_name', config('wikiworld.name')),
            'default_locale' => $this->settings->get('default_locale', config('wikiworld.default_locale')),
            'feature_toggles' => $this->toggles->all(),
        ]);
    }
}
