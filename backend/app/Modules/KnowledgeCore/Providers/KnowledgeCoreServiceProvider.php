<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Providers;

use App\Modules\KnowledgeCore\Application\Actions\CreateEntityDraftAction;
use App\Modules\KnowledgeCore\Application\Actions\RestoreEntityVersionAction;
use App\Modules\KnowledgeCore\Application\Actions\SaveEntityDraftAction;
use App\Modules\KnowledgeCore\Application\Contracts\KnowledgeEntityRepositoryInterface;
use App\Modules\KnowledgeCore\Application\Mappers\EntityEditorialMapper;
use App\Modules\KnowledgeCore\Application\Mappers\EntityPublicMapper;
use App\Modules\KnowledgeCore\Application\Services\AliasService;
use App\Modules\KnowledgeCore\Application\Services\ContentBlockValidator;
use App\Modules\KnowledgeCore\Application\Services\EditorialInsightsService;
use App\Modules\KnowledgeCore\Application\Services\EntityAccessService;
use App\Modules\KnowledgeCore\Application\Services\EntityCacheService;
use App\Modules\KnowledgeCore\Application\Services\EntityLocalizationService;
use App\Modules\KnowledgeCore\Application\Services\EntityQueryService;
use App\Modules\KnowledgeCore\Application\Services\EntitySeoService;
use App\Modules\KnowledgeCore\Application\Services\EntitySlugValidator;
use App\Modules\KnowledgeCore\Application\Services\EntityTypeRegistry;
use App\Modules\KnowledgeCore\Application\Services\EntityVersionService;
use App\Modules\KnowledgeCore\Application\Services\ProfileService;
use App\Modules\KnowledgeCore\Application\Services\ReferenceService;
use App\Modules\KnowledgeCore\Application\Services\RelationshipService;
use App\Modules\KnowledgeCore\Infrastructure\Repositories\EloquentKnowledgeEntityRepository;
use App\Modules\Shared\Providers\ModuleServiceProvider;

final class KnowledgeCoreServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'KnowledgeCore';
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(EntityTypeRegistry::class);
        $this->app->singleton(EntitySlugValidator::class);
        $this->app->singleton(ContentBlockValidator::class);
        $this->app->singleton(EntityLocalizationService::class);
        $this->app->singleton(EntityAccessService::class);
        $this->app->singleton(ProfileService::class);
        $this->app->singleton(RelationshipService::class);
        $this->app->singleton(ReferenceService::class);
        $this->app->singleton(AliasService::class);
        $this->app->singleton(EntityVersionService::class);
        $this->app->singleton(EntityCacheService::class);
        $this->app->singleton(EntityPublicMapper::class);
        $this->app->singleton(EntityEditorialMapper::class);
        $this->app->singleton(EntityQueryService::class);
        $this->app->singleton(EditorialInsightsService::class);
        $this->app->singleton(EntitySeoService::class);
        $this->app->singleton(CreateEntityDraftAction::class);
        $this->app->singleton(SaveEntityDraftAction::class);
        $this->app->singleton(RestoreEntityVersionAction::class);
        $this->app->singleton(KnowledgeEntityRepositoryInterface::class, EloquentKnowledgeEntityRepository::class);
    }
}
