<?php

declare(strict_types=1);

namespace App\Modules\Settings\Providers;

use App\Modules\Settings\Application\Services\FeatureToggleService;
use App\Modules\Settings\Application\Services\SettingsService;
use App\Modules\Shared\Providers\ModuleServiceProvider;

final class SettingsServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Settings';
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(FeatureToggleService::class);
    }

    protected function bootModule(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
