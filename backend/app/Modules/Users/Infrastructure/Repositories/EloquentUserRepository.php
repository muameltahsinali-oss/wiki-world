<?php

declare(strict_types=1);

namespace App\Modules\Users\Infrastructure\Repositories;

use App\Modules\KnowledgeCore\Domain\Support\EntityUuidGenerator;
use App\Modules\Users\Application\Contracts\UserRepositoryInterface;
use App\Modules\Users\Application\DTOs\CreateUserData;
use App\Modules\Users\Application\DTOs\UpdateUserData;
use App\Modules\Users\Application\DTOs\UserData;
use App\Modules\Users\Domain\Entities\User;
use App\Modules\Users\Infrastructure\Mappers\UserMapper;
use App\Modules\Users\Infrastructure\Models\Language;
use App\Modules\Users\Infrastructure\Models\Role;
use App\Modules\Users\Infrastructure\Models\User as UserModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly UserMapper $mapper,
        private readonly EntityUuidGenerator $uuidGenerator,
    ) {}

    public function findByUuid(string $uuid): ?User
    {
        $model = UserModel::query()
            ->with(['preferredLanguage', 'roles'])
            ->where('uuid', $uuid)
            ->first();

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $model = UserModel::query()
            ->with(['preferredLanguage', 'roles'])
            ->where('email', $email)
            ->first();

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function create(CreateUserData $data): User
    {
        $displayName = $data->displayName ?? explode('@', $data->email)[0];

        $model = UserModel::query()->create([
            'uuid' => $this->uuidGenerator->generateString(),
            'name' => $displayName,
            'display_name' => $displayName,
            'email' => $data->email,
            'password_hash' => $data->password,
            'preferred_language_id' => $this->resolveLanguageId($data->preferredLocale),
            'is_active' => true,
            'mfa_enabled' => false,
        ]);

        $model->load(['preferredLanguage', 'roles']);

        return $this->mapper->toDomain($model);
    }

    public function update(string $uuid, UpdateUserData $data): User
    {
        $model = $this->findModelOrFail($uuid);

        $attributes = [];

        if ($data->displayName !== null) {
            $attributes['display_name'] = $data->displayName;
            $attributes['name'] = $data->displayName;
        }

        if ($data->preferredLocale !== null) {
            $attributes['preferred_language_id'] = $this->resolveLanguageId($data->preferredLocale);
        }

        if ($data->isActive !== null) {
            $attributes['is_active'] = $data->isActive;
        }

        if ($data->password !== null) {
            $attributes['password_hash'] = $data->password;
        }

        if ($attributes !== []) {
            $model->update($attributes);
        }

        $model->load(['preferredLanguage', 'roles']);

        return $this->mapper->toDomain($model->fresh());
    }

    public function delete(string $uuid): void
    {
        $this->findModelOrFail($uuid)->delete();
    }

    public function toData(User $user): UserData
    {
        return $this->mapper->toData($user);
    }

    public function assignRole(string $uuid, string $roleCode): User
    {
        $model = $this->findModelOrFail($uuid);
        $roleId = Role::query()->where('code', $roleCode)->value('id');

        if ($roleId === null) {
            throw (new ModelNotFoundException)->setModel(Role::class, [$roleCode]);
        }

        $model->roles()->syncWithoutDetaching([
            $roleId => [
                'assigned_at' => now(),
                'assigned_by_user_id' => null,
            ],
        ]);

        $model = $model->fresh(['preferredLanguage', 'roles']);

        return $this->mapper->toDomain($model);
    }

    public function removeRole(string $uuid, string $roleCode): User
    {
        $model = $this->findModelOrFail($uuid);
        $roleId = Role::query()->where('code', $roleCode)->value('id');

        if ($roleId === null) {
            throw (new ModelNotFoundException)->setModel(Role::class, [$roleCode]);
        }

        $model->roles()->detach($roleId);
        $model = $model->fresh(['preferredLanguage', 'roles']);

        return $this->mapper->toDomain($model);
    }

    public function syncDomainScopes(string $uuid, array $domainCodes): User
    {
        $model = $this->findModelOrFail($uuid);

        \Illuminate\Support\Facades\DB::table('editor_domain_scopes')
            ->where('user_id', $model->id)
            ->delete();

        foreach ($domainCodes as $domainCode) {
            if (! is_string($domainCode) || $domainCode === '') {
                continue;
            }

            \Illuminate\Support\Facades\DB::table('editor_domain_scopes')->insert([
                'user_id' => $model->id,
                'domain_code' => $domainCode,
            ]);
        }

        return $this->mapper->toDomain($model->fresh(['preferredLanguage', 'roles']));
    }

    private function findModelOrFail(string $uuid): UserModel
    {
        $model = UserModel::query()->where('uuid', $uuid)->first();

        if ($model === null) {
            throw (new ModelNotFoundException)->setModel(UserModel::class, [$uuid]);
        }

        return $model;
    }

    private function resolveLanguageId(?string $locale): ?int
    {
        if ($locale === null) {
            return Language::query()->where('is_primary', true)->value('id');
        }

        return Language::query()->where('code', $locale)->value('id');
    }
}
