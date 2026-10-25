<?php

declare(strict_types=1);

namespace App\Modules\Publishing\Providers;

use App\Modules\Publishing\Application\Actions\ArchiveEntityAction;
use App\Modules\Publishing\Application\Actions\PublishEntityAction;
use App\Modules\Publishing\Application\Actions\ReviewSubmissionAction;
use App\Modules\Publishing\Application\Actions\SubmitForReviewAction;
use App\Modules\Publishing\Application\Actions\UnpublishEntityAction;
use App\Modules\Publishing\Application\Listeners\InvalidateEntityCacheOnPublish;
use App\Modules\Publishing\Application\Listeners\RevalidateFrontendOnPublish;
use App\Modules\Publishing\Application\Services\EntityWorkflowPersistenceService;
use App\Modules\Publishing\Application\Services\PublishingWorkflowService;
use App\Modules\Publishing\Application\Services\PublishIntegrityValidator;
use App\Modules\Publishing\Domain\Events\EntityPublished;
use App\Modules\Shared\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Event;

final class PublishingServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Publishing';
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(PublishingWorkflowService::class);
        $this->app->singleton(PublishIntegrityValidator::class);
        $this->app->singleton(EntityWorkflowPersistenceService::class);
        $this->app->singleton(SubmitForReviewAction::class);
        $this->app->singleton(ReviewSubmissionAction::class);
        $this->app->singleton(PublishEntityAction::class);
        $this->app->singleton(UnpublishEntityAction::class);
        $this->app->singleton(ArchiveEntityAction::class);
    }

    public function boot(): void
    {
        parent::boot();

        Event::listen(EntityPublished::class, InvalidateEntityCacheOnPublish::class);
        Event::listen(EntityPublished::class, RevalidateFrontendOnPublish::class);
    }
}
