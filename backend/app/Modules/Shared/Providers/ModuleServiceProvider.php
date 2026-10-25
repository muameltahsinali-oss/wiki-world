<?php

declare(strict_types=1);

namespace App\Modules\Shared\Providers;

use Illuminate\Support\ServiceProvider;

abstract class ModuleServiceProvider extends ServiceProvider
{
    abstract public function moduleName(): string;

    public function register(): void
    {
        $this->registerBindings();
    }

    public function boot(): void
    {
        $this->bootModule();
    }

    protected function registerBindings(): void
    {
        //
    }

    protected function bootModule(): void
    {
        //
    }
}
