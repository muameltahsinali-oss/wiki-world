<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Contracts;

interface AuditLoggerInterface
{
    /**
     * @param  array<string, mixed>|null  $diff
     */
    public function log(
        string $action,
        string $subjectType,
        ?int $subjectId = null,
        ?array $diff = null,
        ?int $actorUserId = null,
    ): void;
}
