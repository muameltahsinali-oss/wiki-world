<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Providers;

use App\Modules\Analytics\Application\Services\AnalyticsIngestService;
use App\Modules\Analytics\Application\Services\PopularEntitiesService;
use App\Modules\Shared\Providers\ModuleServiceProvider;

final class AnalyticsServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Analytics';
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(AnalyticsIngestService::class);
        $this->app->singleton(PopularEntitiesService::class);
    }

    protected function bootModule(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
