<?php

declare(strict_types=1);

namespace App\Modules\Localization\Providers;

use App\Modules\Shared\Providers\ModuleServiceProvider;

final class LocalizationServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Localization';
    }
}
