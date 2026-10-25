<?php

declare(strict_types=1);

namespace App\Modules\Users\Providers;

use App\Modules\Shared\Providers\ModuleServiceProvider;
use App\Modules\Users\Application\Contracts\UserRepositoryInterface;
use App\Modules\Users\Application\Services\AdminUserService;
use App\Modules\Users\Application\Services\BookmarkService;
use App\Modules\Users\Application\Services\ReadingHistoryService;
use App\Modules\Users\Application\Services\SavedSearchService;
use App\Modules\Users\Infrastructure\Repositories\EloquentUserRepository;

final class UsersServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Users';
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->singleton(AdminUserService::class);
        $this->app->singleton(BookmarkService::class);
        $this->app->singleton(ReadingHistoryService::class);
        $this->app->singleton(SavedSearchService::class);
    }
}
