<?php

declare(strict_types=1);

namespace App\Modules\Search\Providers;

use App\Modules\Publishing\Domain\Events\EntityPublished;
use App\Modules\Publishing\Domain\Events\EntityUnpublished;
use App\Modules\Search\Application\Contracts\SearchIndexGatewayInterface;
use App\Modules\Search\Application\Listeners\DeactivateEntityOnUnpublish;
use App\Modules\Search\Application\Listeners\IndexEntityOnPublish;
use App\Modules\Search\Application\Services\SearchIndexerService;
use App\Modules\Search\Application\Services\SearchService;
use App\Modules\Search\Infrastructure\Gateways\DatabaseSearchGateway;
use App\Modules\Search\Infrastructure\Gateways\MeilisearchGateway;
use App\Modules\Shared\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Event;

final class SearchServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Search';
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(DatabaseSearchGateway::class);
        $this->app->singleton(MeilisearchGateway::class);

        $this->app->singleton(SearchIndexGatewayInterface::class, function ($app) {
            $driver = config('wikiworld.search.driver', 'database');

            return match ($driver) {
                'meilisearch' => $app->make(MeilisearchGateway::class),
                default => $app->make(DatabaseSearchGateway::class),
            };
        });

        $this->app->singleton(SearchIndexerService::class);
        $this->app->singleton(SearchService::class);
    }

    protected function bootModule(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        Event::listen(EntityPublished::class, IndexEntityOnPublish::class);
        Event::listen(EntityUnpublished::class, DeactivateEntityOnUnpublish::class);
    }
}
