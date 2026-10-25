<?php

declare(strict_types=1);

namespace App\Modules\SEO\Providers;

use App\Modules\Publishing\Domain\Events\EntityPublished;
use App\Modules\SEO\Application\Listeners\UpsertSeoOnPublish;
use App\Modules\SEO\Application\Services\RedirectService;
use App\Modules\SEO\Application\Services\SitemapService;
use App\Modules\SEO\Application\Services\StructuredDataService;
use App\Modules\Shared\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Event;

final class SEOServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'SEO';
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(StructuredDataService::class);
        $this->app->singleton(SitemapService::class);
        $this->app->singleton(RedirectService::class);
    }

    protected function bootModule(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        Event::listen(EntityPublished::class, UpsertSeoOnPublish::class);
    }
}
