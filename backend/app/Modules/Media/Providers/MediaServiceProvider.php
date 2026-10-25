<?php

declare(strict_types=1);

namespace App\Modules\Media\Providers;

use App\Modules\Media\Application\Services\MediaService;
use App\Modules\Shared\Providers\ModuleServiceProvider;

final class MediaServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Media';
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(MediaService::class);
    }
}
