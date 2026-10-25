<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Modules\Audit\Application\Contracts\AuditLoggerInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DatabaseAuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_logger_persists_entry(): void
    {
        $logger = $this->app->make(AuditLoggerInterface::class);

        $logger->log('entity.updated', 'entity', 42, ['title' => ['old' => 'A', 'new' => 'B']], null);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'entity.updated',
            'subject_type' => 'entity',
            'subject_id' => 42,
        ]);
    }
}
