<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Services;

use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\KnowledgeCore\Infrastructure\Models\Entity;
use App\Modules\RolesPermissions\Application\Contracts\AuthorizationServiceInterface;
use App\Modules\RolesPermissions\Domain\Enums\Role;
use App\Modules\Users\Infrastructure\Models\Language;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class EditorialInsightsService
{
    public function __construct(
        private readonly AuthorizationServiceInterface $authorization,
        private readonly EntityQueryService $queries,
    ) {}

    /**
     * @return array<string, int>
     */
    public function dashboard(User $user): array
    {
        $query = $this->scopedEntityQuery($user);

        return [
            'drafts_count' => (clone $query)->where('entities.workflow_state', WorkflowState::Draft->value)->count(),
            'in_review_count' => (clone $query)->where('entities.workflow_state', WorkflowState::InReview->value)->count(),
            'approved_count' => (clone $query)->where('entities.workflow_state', WorkflowState::Approved->value)->count(),
            'published_this_week' => (clone $query)
                ->where('entities.workflow_state', WorkflowState::Published->value)
                ->where('entities.published_at', '>=', Carbon::now()->subWeek())
                ->count(),
        ];
    }

    /**
     * @return array{data: list<array<string, mixed>>, pagination: array<string, int>}
     */
    public function drafts(User $user, string $locale, int $page, int $perPage): array
    {
        return $this->queries->listEditorial([
            'locale' => $locale,
            'workflow_state' => WorkflowState::Draft->value,
            'domain' => $this->singleDomainFilter($user),
        ], $page, $perPage);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function submissions(User $user, int $limit = 50): array
    {
        $query = DB::table('editorial_submissions as s')
            ->join('entities as e', 'e.id', '=', 's.entity_id')
            ->join('users as u', 'u.id', '=', 's.submitted_by_user_id')
            ->select([
                's.id',
                's.status',
                's.submitted_at',
                'e.uuid as entity_uuid',
                'e.workflow_state',
                'u.uuid as submitted_by_uuid',
            ])
            ->orderByDesc('s.submitted_at')
            ->limit(min($limit, 50));

        $this->applyDomainScopeToSubmissionQuery($query, $user);

        return $query->get()->map(static fn (object $row): array => [
            'id' => (int) $row->id,
            'status' => $row->status,
            'submitted_at' => Carbon::parse($row->submitted_at)->toIso8601String(),
            'entity_uuid' => $row->entity_uuid,
            'workflow_state' => $row->workflow_state,
            'submitted_by_uuid' => $row->submitted_by_uuid,
        ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function reviewHistory(User $user, int $limit = 50): array
    {
        $query = DB::table('editorial_reviews as r')
            ->join('editorial_submissions as s', 's.id', '=', 'r.submission_id')
            ->join('entities as e', 'e.id', '=', 's.entity_id')
            ->join('users as u', 'u.id', '=', 'r.reviewer_user_id')
            ->select([
                'r.id',
                'r.decision',
                'r.decision_notes',
                'r.reviewed_at',
                's.id as submission_id',
                'e.uuid as entity_uuid',
                'u.uuid as reviewer_uuid',
            ])
            ->orderByDesc('r.reviewed_at')
            ->limit(min($limit, 50));

        $this->applyDomainScopeToSubmissionQuery($query, $user, 'e');

        return $query->get()->map(static fn (object $row): array => [
            'id' => (int) $row->id,
            'submission_id' => (int) $row->submission_id,
            'decision' => $row->decision,
            'decision_notes' => $row->decision_notes,
            'reviewed_at' => Carbon::parse($row->reviewed_at)->toIso8601String(),
            'entity_uuid' => $row->entity_uuid,
            'reviewer_uuid' => $row->reviewer_uuid,
        ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function activity(User $user, ?string $action = null, int $limit = 50): array
    {
        $query = DB::table('audit_logs')
            ->where('subject_type', 'entity')
            ->orderByDesc('created_at')
            ->limit(min($limit, 50));

        if ($action !== null && $action !== '') {
            $query->where('action', $action);
        }

        return $query->get()->map(static fn (object $row): array => [
            'id' => (int) $row->id,
            'action' => $row->action,
            'subject_type' => $row->subject_type,
            'subject_id' => (int) $row->subject_id,
            'actor_user_id' => $row->actor_user_id !== null ? (int) $row->actor_user_id : null,
            'created_at' => Carbon::parse($row->created_at)->toIso8601String(),
            'diff' => json_decode((string) $row->diff, true),
        ])->all();
    }

    /**
     * @return array{data: list<array<string, mixed>>, pagination: array<string, int>}
     */
    public function searchEntities(User $user, string $locale, string $query, int $page, int $perPage): array
    {
        return $this->queries->listEditorial([
            'locale' => $locale,
            'q' => $query,
            'domain' => $this->singleDomainFilter($user),
        ], $page, $perPage);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function translationsQueue(User $user, string $locale): array
    {
        $languageId = Language::query()->where('code', $locale)->value('id');

        $query = DB::table('entity_localizations as el')
            ->join('entities as e', 'e.id', '=', 'el.entity_id')
            ->where('el.language_id', $languageId)
            ->where('el.translation_status', 'pending')
            ->select([
                'e.uuid as entity_uuid',
                'el.title',
                'el.translation_status',
                'el.updated_at',
            ])
            ->orderByDesc('el.updated_at')
            ->limit(50);

        $this->applyDomainScopeToEntityTable($query, $user, 'e');

        return $query->get()->map(static fn (object $row): array => [
            'entity_uuid' => $row->entity_uuid,
            'title' => $row->title,
            'translation_status' => $row->translation_status,
            'updated_at' => Carbon::parse($row->updated_at)->toIso8601String(),
        ])->all();
    }

    /**
     * @return array<string, int>
     */
    public function analytics(User $user): array
    {
        $entityQuery = $this->scopedEntityQuery($user);

        return [
            'total_entities' => (clone $entityQuery)->count(),
            'published_entities' => (clone $entityQuery)
                ->where('entities.workflow_state', WorkflowState::Published->value)
                ->count(),
            'publish_events_this_month' => DB::table('publish_events as pe')
                ->join('entities as e', 'e.id', '=', 'pe.entity_id')
                ->when(! $this->isGlobalEditor($user), function ($q) use ($user): void {
                    $q->where(function ($inner) use ($user): void {
                        foreach ($this->userDomains($user) as $domain) {
                            $inner->orWhere('e.module_flags->domain', $domain);
                        }
                    });
                })
                ->where('pe.action', 'publish')
                ->where('pe.performed_at', '>=', Carbon::now()->startOfMonth())
                ->count(),
        ];
    }

    private function scopedEntityQuery(User $user): Builder
    {
        $query = Entity::query();

        if (! $this->isGlobalEditor($user)) {
            $domains = $this->userDomains($user);
            $query->where(function (Builder $inner) use ($domains): void {
                foreach ($domains as $domain) {
                    $inner->orWhere('module_flags->domain', $domain);
                }
            });
        }

        return $query;
    }

    private function isGlobalEditor(User $user): bool
    {
        return $this->authorization->userHasRole($user, Role::SuperAdmin->value)
            || $this->authorization->userHasRole($user, Role::SeniorEditor->value);
    }

    /**
     * @return list<string>
     */
    private function userDomains(User $user): array
    {
        return DB::table('editor_domain_scopes')
            ->where('user_id', $user->id)
            ->pluck('domain_code')
            ->all();
    }

    private function singleDomainFilter(User $user): ?string
    {
        if ($this->isGlobalEditor($user)) {
            return null;
        }

        $domains = $this->userDomains($user);

        return $domains[0] ?? null;
    }

    /**
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    private function applyDomainScopeToSubmissionQuery($query, User $user, string $alias = 'e'): void
    {
        if ($this->isGlobalEditor($user)) {
            return;
        }

        $domains = $this->userDomains($user);
        $query->where(function ($inner) use ($domains, $alias): void {
            foreach ($domains as $domain) {
                $inner->orWhere("{$alias}.module_flags->domain", $domain);
            }
        });
    }

    /**
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    private function applyDomainScopeToEntityTable($query, User $user, string $alias = 'e'): void
    {
        if ($this->isGlobalEditor($user)) {
            return;
        }

        $domains = $this->userDomains($user);
        $query->where(function ($inner) use ($domains, $alias): void {
            foreach ($domains as $domain) {
                $inner->orWhere("{$alias}.module_flags->domain", $domain);
            }
        });
    }
}
