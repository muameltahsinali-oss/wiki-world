<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Application\Actions;

use App\Modules\Authentication\Application\DTOs\LoginResult;
use App\Modules\Authentication\Domain\Exceptions\AccountLockedException;
use App\Modules\Authentication\Domain\Exceptions\InvalidCredentialsException;
use App\Modules\RolesPermissions\Application\Services\RoleResolver;
use App\Modules\Shared\Application\Contracts\RateLimiterGatewayInterface;
use App\Modules\Users\Application\Contracts\UserRepositoryInterface;
use App\Modules\Users\Infrastructure\Models\User as UserModel;
use Illuminate\Support\Facades\Hash;

final class LoginUserAction
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 900;

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly RoleResolver $roleResolver,
        private readonly RateLimiterGatewayInterface $rateLimiter,
    ) {}

    public function execute(string $email, string $password, ?string $deviceName = null): LoginResult
    {
        $rateLimitKey = 'login:'.sha1(strtolower($email));

        if ($this->rateLimiter->tooManyAttempts($rateLimitKey, self::MAX_ATTEMPTS)) {
            throw new AccountLockedException;
        }

        $model = UserModel::query()
            ->where('email', $email)
            ->first();

        if ($model === null || ! Hash::check($password, $model->password_hash)) {
            $this->rateLimiter->hit($rateLimitKey, self::DECAY_SECONDS);

            throw new InvalidCredentialsException;
        }

        if (! $model->is_active) {
            $this->rateLimiter->hit($rateLimitKey, self::DECAY_SECONDS);

            throw new InvalidCredentialsException;
        }

        $this->rateLimiter->clear($rateLimitKey);

        $model->update(['last_login_at' => now()]);

        $abilities = $this->roleResolver->tokenAbilitiesForUser($model);
        $token = $model->createToken($deviceName ?? 'api', $abilities)->plainTextToken;

        $user = $this->users->findByUuid($model->uuid);

        if ($user === null) {
            throw new InvalidCredentialsException;
        }

        return new LoginResult(
            token: $token,
            tokenType: 'Bearer',
            expiresAt: null,
            user: $this->users->toData($user),
        );
    }
}
