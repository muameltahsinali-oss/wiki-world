<?php

declare(strict_types=1);

namespace App\Modules\Shared\Providers;

use App\Modules\Ahlulbayt\Providers\AhlulbaytServiceProvider;
use App\Modules\AI\Providers\AIServiceProvider;
use App\Modules\Analytics\Providers\AnalyticsServiceProvider;
use App\Modules\Audit\Providers\AuditServiceProvider;
use App\Modules\Authentication\Providers\AuthenticationServiceProvider;
use App\Modules\KnowledgeCore\Providers\KnowledgeCoreServiceProvider;
use App\Modules\Localization\Providers\LocalizationServiceProvider;
use App\Modules\Media\Providers\MediaServiceProvider;
use App\Modules\Publishing\Providers\PublishingServiceProvider;
use App\Modules\Quran\Providers\QuranServiceProvider;
use App\Modules\RolesPermissions\Providers\RolesPermissionsServiceProvider;
use App\Modules\Search\Providers\SearchServiceProvider;
use App\Modules\SEO\Providers\SEOServiceProvider;
use App\Modules\Settings\Providers\SettingsServiceProvider;
use App\Modules\Users\Providers\UsersServiceProvider;
use Illuminate\Support\ServiceProvider;

final class WikiWorldServiceProvider extends ServiceProvider
{
    /** @var list<class-string<ModuleServiceProvider>> */
    private array $modules = [
        AuthenticationServiceProvider::class,
        UsersServiceProvider::class,
        RolesPermissionsServiceProvider::class,
        KnowledgeCoreServiceProvider::class,
        PublishingServiceProvider::class,
        LocalizationServiceProvider::class,
        SearchServiceProvider::class,
        QuranServiceProvider::class,
        AhlulbaytServiceProvider::class,
        AIServiceProvider::class,
        MediaServiceProvider::class,
        SEOServiceProvider::class,
        AnalyticsServiceProvider::class,
        SettingsServiceProvider::class,
        AuditServiceProvider::class,
    ];

    public function register(): void
    {
        $this->app->register(SharedInfrastructureServiceProvider::class);

        foreach ($this->modules as $module) {
            $this->app->register($module);
        }
    }
}
