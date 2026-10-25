<?php

declare(strict_types=1);

namespace App\Modules\Audit\Providers;

use App\Modules\Audit\Application\Contracts\AuditLoggerInterface;
use App\Modules\Audit\Application\Services\AuditQueryService;
use App\Modules\Audit\Infrastructure\Services\DatabaseAuditLogger;
use App\Modules\Shared\Providers\ModuleServiceProvider;

final class AuditServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Audit';
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(AuditLoggerInterface::class, DatabaseAuditLogger::class);
        $this->app->singleton(AuditQueryService::class);
    }
}
