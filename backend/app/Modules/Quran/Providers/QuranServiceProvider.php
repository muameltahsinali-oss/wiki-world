<?php

declare(strict_types=1);

namespace App\Modules\Quran\Providers;

use App\Modules\Quran\Application\Services\QuranService;
use App\Modules\Shared\Providers\ModuleServiceProvider;

final class QuranServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Quran';
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(QuranService::class);
    }

    protected function bootModule(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
