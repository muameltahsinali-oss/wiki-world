<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\Services;

use App\Modules\Audit\Application\Contracts\AuditLoggerInterface;
use App\Modules\Users\Application\Contracts\UserRepositoryInterface;
use App\Modules\Users\Application\DTOs\CreateUserData;
use App\Modules\Users\Application\DTOs\UpdateUserData;
use App\Modules\Users\Infrastructure\Models\User as UserModel;
use Illuminate\Support\Facades\DB;

final class AdminUserService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AuditLoggerInterface $audit,
    ) {}

    /**
     * @return array{data: list<array<string, mixed>>, pagination: array<string, int>}
     */
    public function list(int $page, int $perPage, ?string $query = null): array
    {
        $builder = UserModel::query()
            ->with(['preferredLanguage', 'roles'])
            ->orderByDesc('created_at');

        if ($query !== null && $query !== '') {
            $builder->where(function ($q) use ($query): void {
                $q->where('email', 'like', '%'.$query.'%')
                    ->orWhere('display_name', 'like', '%'.$query.'%');
            });
        }

        $paginator = $builder->paginate($perPage, ['*'], 'page', $page);

        $data = collect($paginator->items())->map(fn (UserModel $user): array => $this->userPayload($user->uuid))->all();

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

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(array $payload, ?int $actorUserId = null): array
    {
        $user = $this->users->create(new CreateUserData(
            email: $payload['email'],
            password: $payload['password'],
            displayName: $payload['display_name'] ?? null,
            preferredLocale: $payload['preferred_locale'] ?? null,
        ));

        if (isset($payload['role']) && is_string($payload['role'])) {
            $user = $this->users->assignRole($user->uuid, $payload['role']);
        }

        if (isset($payload['domain_scopes']) && is_array($payload['domain_scopes'])) {
            $this->users->syncDomainScopes($user->uuid, $payload['domain_scopes']);
        }

        $this->audit->log('user.create', 'user', $this->resolveUserId($user->uuid), ['email' => $payload['email']], $actorUserId);

        return $this->userPayload($user->uuid);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(string $uuid, array $payload, ?int $actorUserId = null): array
    {
        $this->users->update($uuid, new UpdateUserData(
            displayName: $payload['display_name'] ?? null,
            preferredLocale: $payload['preferred_locale'] ?? null,
            isActive: isset($payload['is_active']) ? (bool) $payload['is_active'] : null,
            password: $payload['password'] ?? null,
        ));

        $this->audit->log('user.update', 'user', $this->resolveUserId($uuid), $payload, $actorUserId);

        return $this->userPayload($uuid);
    }

    /**
     * @return array<string, mixed>
     */
    public function assignRole(string $uuid, string $roleCode, ?int $actorUserId = null): array
    {
        $this->users->assignRole($uuid, $roleCode);
        $this->audit->log('role.assign', 'user', $this->resolveUserId($uuid), ['role' => $roleCode], $actorUserId);

        return $this->userPayload($uuid);
    }

    /**
     * @return array<string, mixed>
     */
    public function removeRole(string $uuid, string $roleCode, ?int $actorUserId = null): array
    {
        $this->users->removeRole($uuid, $roleCode);
        $this->audit->log('role.remove', 'user', $this->resolveUserId($uuid), ['role' => $roleCode], $actorUserId);

        return $this->userPayload($uuid);
    }

    /**
     * @param  list<string>  $domains
     * @return array<string, mixed>
     */
    public function syncDomainScopes(string $uuid, array $domains, ?int $actorUserId = null): array
    {
        $this->users->syncDomainScopes($uuid, $domains);
        $this->audit->log('user.domain_scopes', 'user', $this->resolveUserId($uuid), ['domains' => $domains], $actorUserId);

        return $this->userPayload($uuid);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(string $uuid): array
    {
        $user = $this->users->findByUuid($uuid);
        if ($user === null) {
            throw new \RuntimeException('User not found');
        }

        $domainScopes = DB::table('editor_domain_scopes')
            ->where('user_id', $user->id())
            ->pluck('domain_code')
            ->values()
            ->all();

        return array_merge($this->users->toData($user)->toArray(), [
            'domain_scopes' => $domainScopes,
        ]);
    }

    private function resolveUserId(string $uuid): ?int
    {
        return UserModel::query()->where('uuid', $uuid)->value('id');
    }
}
