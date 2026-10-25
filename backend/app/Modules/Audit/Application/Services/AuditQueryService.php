<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Services;

use Illuminate\Support\Facades\DB;

final class AuditQueryService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{data: list<array<string, mixed>>, pagination: array<string, int>}
     */
    public function list(array $filters, int $page, int $perPage): array
    {
        $query = DB::table('audit_logs as al')
            ->leftJoin('users as u', 'u.id', '=', 'al.actor_user_id')
            ->select([
                'al.id',
                'al.action',
                'al.subject_type',
                'al.subject_id',
                'al.diff',
                'al.ip_address',
                'al.created_at',
                'u.uuid as actor_uuid',
                'u.email as actor_email',
            ])
            ->orderByDesc('al.created_at');

        if (isset($filters['action']) && is_string($filters['action'])) {
            $query->where('al.action', $filters['action']);
        }

        if (isset($filters['subject_type']) && is_string($filters['subject_type'])) {
            $query->where('al.subject_type', $filters['subject_type']);
        }

        if (isset($filters['actor_user_id']) && is_numeric($filters['actor_user_id'])) {
            $query->where('al.actor_user_id', (int) $filters['actor_user_id']);
        }

        if (isset($filters['from']) && is_string($filters['from'])) {
            $query->where('al.created_at', '>=', $filters['from']);
        }

        if (isset($filters['to']) && is_string($filters['to'])) {
            $query->where('al.created_at', '<=', $filters['to']);
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $data = collect($paginator->items())->map(static function ($row): array {
            return [
                'id' => $row->id,
                'action' => $row->action,
                'subject_type' => $row->subject_type,
                'subject_id' => $row->subject_id,
                'diff' => $row->diff !== null ? json_decode($row->diff, true) : null,
                'ip_address' => $row->ip_address,
                'created_at' => $row->created_at,
                'actor' => $row->actor_uuid !== null ? [
                    'uuid' => $row->actor_uuid,
                    'email' => $row->actor_email,
                ] : null,
            ];
        })->all();

        return [
            'data' => $data,
            'pagination' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }
}
