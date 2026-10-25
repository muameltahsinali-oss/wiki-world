<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Providers;

use App\Modules\Authentication\Application\Actions\LoginUserAction;
use App\Modules\Authentication\Application\Actions\LogoutUserAction;
use App\Modules\Authentication\Application\Actions\RegisterUserAction;
use App\Modules\Shared\Providers\ModuleServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

final class AuthenticationServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Authentication';
    }

    public function boot(): void
    {
        ResetPassword::createUrlUsing(static function (object $user, string $token): string {
            return url('/api/v1/auth/reset-password').'?'.http_build_query([
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]);
        });
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(RegisterUserAction::class);
        $this->app->singleton(LoginUserAction::class);
        $this->app->singleton(LogoutUserAction::class);
    }
}
