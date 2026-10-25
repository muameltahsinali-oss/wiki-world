<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Controllers\Admin;

use App\Modules\Audit\Application\Contracts\AuditLoggerInterface;
use App\Modules\Settings\Application\Services\SettingsService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PatchAdminSettingsController
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly AuditLoggerInterface $audit,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'maintenance_mode' => ['nullable', 'boolean'],
            'site_name' => ['nullable', 'string', 'max:255'],
            'default_locale' => ['nullable', 'string', 'in:ar,en'],
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                $this->settings->set($key, $value, 'platform', isPublic: $key === 'site_name');
            }
        }

        $this->audit->log('settings.update', 'site_settings', null, $validated, $request->user()?->id);

        return ApiResponse::success([
            'maintenance_mode' => (bool) $this->settings->get('maintenance_mode', false),
            'site_name' => $this->settings->get('site_name', config('wikiworld.name')),
            'default_locale' => $this->settings->get('default_locale', config('wikiworld.default_locale')),
        ]);
    }
}
