<?php

declare(strict_types=1);

namespace App\Modules\Audit\Http\Controllers\Admin;

use App\Modules\Audit\Application\Services\AuditQueryService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ListAdminAuditLogsController
{
    public function __construct(
        private readonly AuditQueryService $audit,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $filters = array_filter([
            'action' => $request->input('filter.action'),
            'subject_type' => $request->input('filter.subject_type'),
            'actor_user_id' => $request->input('filter.actor_user_id'),
            'from' => $request->input('filter.from'),
            'to' => $request->input('filter.to'),
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(1, (int) $request->input('per_page', 20)));

        $result = $this->audit->list($filters, $page, $perPage);

        return ApiResponse::success($result['data'], meta: ['pagination' => $result['pagination']]);
    }
}
