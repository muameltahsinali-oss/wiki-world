<?php

declare(strict_types=1);

namespace App\Modules\RolesPermissions\Providers;

use App\Modules\RolesPermissions\Application\Contracts\AuthorizationServiceInterface;
use App\Modules\RolesPermissions\Application\Services\RoleResolver;
use App\Modules\RolesPermissions\Infrastructure\Services\AuthorizationService;
use App\Modules\Shared\Providers\ModuleServiceProvider;

final class RolesPermissionsServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'RolesPermissions';
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(RoleResolver::class);
        $this->app->singleton(AuthorizationServiceInterface::class, AuthorizationService::class);
    }
}
