<?php

declare(strict_types=1);

namespace App\Modules\Audit\Infrastructure\Services;

use App\Modules\Audit\Application\Contracts\AuditLoggerInterface;
use Illuminate\Support\Facades\DB;

final class DatabaseAuditLogger implements AuditLoggerInterface
{
    public function log(
        string $action,
        string $subjectType,
        ?int $subjectId = null,
        ?array $diff = null,
        ?int $actorUserId = null,
    ): void {
        DB::table('audit_logs')->insert([
            'actor_user_id' => $actorUserId,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'diff' => $diff !== null ? json_encode($diff, JSON_THROW_ON_ERROR) : null,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
