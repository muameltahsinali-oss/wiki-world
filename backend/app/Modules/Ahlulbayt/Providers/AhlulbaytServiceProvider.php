<?php

declare(strict_types=1);

namespace App\Modules\Ahlulbayt\Providers;

use App\Modules\Ahlulbayt\Application\Services\AhlulbaytService;
use App\Modules\Shared\Providers\ModuleServiceProvider;

final class AhlulbaytServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Ahlulbayt';
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(AhlulbaytService::class);
    }
}
