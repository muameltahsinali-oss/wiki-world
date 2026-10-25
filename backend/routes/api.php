<?php

declare(strict_types=1);

use App\Http\Controllers\HealthController;
use App\Modules\Analytics\Http\Controllers\AdminAnalyticsOverviewController;
use App\Modules\Analytics\Http\Controllers\PopularAnalyticsController;
use App\Modules\Analytics\Http\Controllers\RecordViewController;
use App\Modules\Ahlulbayt\Http\Controllers\AhlulbaytBrowseController;
use App\Modules\Ahlulbayt\Http\Controllers\AhlulbaytGenealogyController;
use App\Modules\Ahlulbayt\Http\Controllers\AhlulbaytLandingController;
use App\Modules\Ahlulbayt\Http\Controllers\AhlulbaytLineageController;
use App\Modules\Ahlulbayt\Http\Controllers\AhlulbaytTimelineController;
use App\Modules\AI\Http\Controllers\ChatController;
use App\Modules\AI\Http\Controllers\ConversationsController;
use App\Modules\AI\Http\Controllers\EditorialAiFlagsController;
use App\Modules\AI\Http\Controllers\FlagMessageController;
use App\Modules\AI\Http\Controllers\RecommendationsController;
use App\Modules\Audit\Http\Controllers\Admin\ListAdminAuditLogsController;
use App\Modules\Authentication\Http\Controllers\ForgotPasswordController;
use App\Modules\Authentication\Http\Controllers\LoginController;
use App\Modules\Authentication\Http\Controllers\LogoutController;
use App\Modules\Authentication\Http\Controllers\MeController;
use App\Modules\Authentication\Http\Controllers\RegisterController;
use App\Modules\Authentication\Http\Controllers\ResetPasswordController;
use App\Modules\Authentication\Http\Controllers\VerifyEmailController;
use App\Modules\Authentication\Http\Middleware\EnsureEmailIsVerified;
use App\Modules\KnowledgeCore\Http\Controllers\CreateEntityDraftController;
use App\Modules\KnowledgeCore\Http\Controllers\CreateEntityReferenceController;
use App\Modules\KnowledgeCore\Http\Controllers\CreateEntityRelationshipController;
use App\Modules\KnowledgeCore\Http\Controllers\DeleteEntityReferenceController;
use App\Modules\KnowledgeCore\Http\Controllers\DeleteEntityRelationshipController;
use App\Modules\KnowledgeCore\Http\Controllers\EditorialActivityController;
use App\Modules\KnowledgeCore\Http\Controllers\EditorialAnalyticsController;
use App\Modules\KnowledgeCore\Http\Controllers\EditorialDashboardController;
use App\Modules\KnowledgeCore\Http\Controllers\EditorialDraftsController;
use App\Modules\KnowledgeCore\Http\Controllers\EditorialReviewHistoryController;
use App\Modules\KnowledgeCore\Http\Controllers\EditorialSearchEntitiesController;
use App\Modules\KnowledgeCore\Http\Controllers\EditorialSettingsController;
use App\Modules\KnowledgeCore\Http\Controllers\EditorialSubmissionsController;
use App\Modules\KnowledgeCore\Http\Controllers\EditorialTranslationsQueueController;
use App\Modules\KnowledgeCore\Http\Controllers\GetEditorialEntityController;
use App\Modules\KnowledgeCore\Http\Controllers\GetEntityPreviewController;
use App\Modules\KnowledgeCore\Http\Controllers\GetEntitySeoController;
use App\Modules\KnowledgeCore\Http\Controllers\GetEntityVersionController;
use App\Modules\KnowledgeCore\Http\Controllers\GetPublicEntityController;
use App\Modules\KnowledgeCore\Http\Controllers\GetPublicEntityRelationshipsController;
use App\Modules\KnowledgeCore\Http\Controllers\GetPublicTypeEntityController;
use App\Modules\KnowledgeCore\Http\Controllers\ListBookChaptersController;
use App\Modules\KnowledgeCore\Http\Controllers\ListEditorialEntitiesController;
use App\Modules\KnowledgeCore\Http\Controllers\ListEntityReferencesController;
use App\Modules\KnowledgeCore\Http\Controllers\ListEntityRelationshipsController;
use App\Modules\KnowledgeCore\Http\Controllers\ListEntityVersionsController;
use App\Modules\KnowledgeCore\Http\Controllers\ListPublicEntitiesController;
use App\Modules\KnowledgeCore\Http\Controllers\PatchEntitySeoController;
use App\Modules\KnowledgeCore\Http\Controllers\RestoreEntityVersionController;
use App\Modules\KnowledgeCore\Http\Controllers\SaveEntityDraftController;
use App\Modules\Media\Http\Controllers\AttachEntityMediaController;
use App\Modules\Media\Http\Controllers\GetMediaController;
use App\Modules\Media\Http\Controllers\ListMediaController;
use App\Modules\Media\Http\Controllers\MediaSignedUrlController;
use App\Modules\Media\Http\Controllers\PatchMediaController;
use App\Modules\Media\Http\Controllers\UploadMediaController;
use App\Modules\Publishing\Http\Controllers\ArchiveEntityController;
use App\Modules\Publishing\Http\Controllers\GetPublishReadinessController;
use App\Modules\Publishing\Http\Controllers\PublishEntityController;
use App\Modules\Publishing\Http\Controllers\ReviewSubmissionController;
use App\Modules\Publishing\Http\Controllers\SubmitEntityController;
use App\Modules\Publishing\Http\Controllers\UnpublishEntityController;
use App\Modules\Quran\Http\Controllers\GetSurahController;
use App\Modules\Quran\Http\Controllers\GetVerseController;
use App\Modules\Quran\Http\Controllers\GetVerseCrossReferencesController;
use App\Modules\Quran\Http\Controllers\ListQuranTopicsController;
use App\Modules\Quran\Http\Controllers\ListQuranTranslationsController;
use App\Modules\Quran\Http\Controllers\ListSurahsController;
use App\Modules\Quran\Http\Controllers\ListSurahVersesController;
use App\Modules\Quran\Http\Controllers\ListVerseTafsirController;
use App\Modules\RolesPermissions\Http\Middleware\EnsureAdminAccess;
use App\Modules\RolesPermissions\Http\Middleware\EnsureEditorialAccess;
use App\Modules\Settings\Http\Controllers\GetPublicSiteConfigController;
use App\Modules\Settings\Http\Middleware\EnsureFeatureEnabled;
use App\Modules\Settings\Http\Controllers\Admin\GetAdminSettingsController;
use App\Modules\Settings\Http\Controllers\Admin\PatchAdminSettingsController;
use App\Modules\Settings\Http\Controllers\Admin\PatchFeatureTogglesController;
use App\Modules\Search\Http\Controllers\QuranSearchController;
use App\Modules\Search\Http\Controllers\SearchController;
use App\Modules\Search\Http\Controllers\SearchRelatedController;
use App\Modules\Search\Http\Controllers\SearchSuggestionsController;
use App\Modules\SEO\Http\Controllers\AdminRedirectsController;
use App\Modules\SEO\Http\Controllers\SitemapController;
use App\Modules\Shared\Http\Middleware\LocaleMiddleware;
use App\Modules\Shared\Http\Middleware\RequestIdMiddleware;
use App\Modules\Users\Http\Controllers\Admin\AssignUserRoleController;
use App\Modules\Users\Http\Controllers\Admin\CreateAdminUserController;
use App\Modules\Users\Http\Controllers\Admin\ListAdminUsersController;
use App\Modules\Users\Http\Controllers\Admin\RemoveUserRoleController;
use App\Modules\Users\Http\Controllers\Admin\UpdateAdminUserController;
use App\Modules\Users\Http\Controllers\Admin\UpdateUserDomainScopesController;
use App\Modules\Users\Http\Controllers\BookmarksController;
use App\Modules\Users\Http\Controllers\ReadingHistoryController;
use App\Modules\Users\Http\Controllers\SavedSearchesController;
use Illuminate\Support\Facades\Route;

Route::middleware([RequestIdMiddleware::class])->group(function (): void {
    Route::get('/health', [HealthController::class, 'health']);
    Route::get('/health/ready', [HealthController::class, 'ready']);

    Route::prefix('v1')->group(function (): void {
        Route::get('/site/config', GetPublicSiteConfigController::class);

        Route::prefix('auth')->group(function (): void {
            Route::post('/register', RegisterController::class)->middleware('feature:registration_enabled');
            Route::post('/login', LoginController::class);
            Route::post('/forgot-password', ForgotPasswordController::class);
            Route::post('/reset-password', ResetPasswordController::class);
            Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['signed'])
                ->name('verification.verify');
            Route::post('/logout', LogoutController::class)->middleware(['auth:sanctum']);
        });

        Route::middleware(['auth:sanctum', EnsureEmailIsVerified::class])->group(function (): void {
            Route::get('/me', [MeController::class, 'show']);
            Route::patch('/me', [MeController::class, 'update']);
            Route::delete('/me', [MeController::class, 'destroy']);
            Route::get('/me/bookmarks', [BookmarksController::class, 'index']);
            Route::post('/me/bookmarks', [BookmarksController::class, 'store']);
            Route::delete('/me/bookmarks/{entityUuid}', [BookmarksController::class, 'destroy']);
            Route::get('/me/history', [ReadingHistoryController::class, 'index']);
            Route::post('/me/history', [ReadingHistoryController::class, 'store']);
            Route::delete('/me/history', [ReadingHistoryController::class, 'destroy']);
            Route::get('/me/saved-searches', [SavedSearchesController::class, 'index']);
            Route::post('/me/saved-searches', [SavedSearchesController::class, 'store']);
            Route::delete('/me/saved-searches/{uuid}', [SavedSearchesController::class, 'destroy']);
            Route::get('/me/ai/conversations', [ConversationsController::class, 'index']);
            Route::get('/me/ai/conversations/{uuid}', [ConversationsController::class, 'show']);
            Route::delete('/me/ai/conversations/{uuid}', [ConversationsController::class, 'destroy']);
        });

        Route::prefix('editorial')->middleware(['auth:sanctum', EnsureEmailIsVerified::class, EnsureEditorialAccess::class])->group(function (): void {
            Route::get('/status', static fn () => response()->json(['data' => ['status' => 'editorial-ready']]));
            Route::get('/dashboard', EditorialDashboardController::class);
            Route::get('/drafts', EditorialDraftsController::class);
            Route::get('/submissions', EditorialSubmissionsController::class);
            Route::get('/reviews/history', EditorialReviewHistoryController::class);
            Route::get('/activity', EditorialActivityController::class);
            Route::get('/search/entities', EditorialSearchEntitiesController::class);
            Route::get('/settings', EditorialSettingsController::class);
            Route::get('/translations/queue', EditorialTranslationsQueueController::class);
            Route::get('/analytics', EditorialAnalyticsController::class);
            Route::get('/media', ListMediaController::class);
            Route::post('/media', UploadMediaController::class);
            Route::get('/media/{uuid}', GetMediaController::class);
            Route::patch('/media/{uuid}', PatchMediaController::class);
            Route::post('/media/{uuid}/signed-url', MediaSignedUrlController::class);
            Route::get('/entities', ListEditorialEntitiesController::class);
            Route::post('/entities', CreateEntityDraftController::class);
            Route::get('/entities/{uuid}', GetEditorialEntityController::class);
            Route::get('/entities/{uuid}/preview', GetEntityPreviewController::class);
            Route::get('/entities/{uuid}/publish-readiness', GetPublishReadinessController::class);
            Route::get('/entities/{uuid}/seo', GetEntitySeoController::class);
            Route::patch('/entities/{uuid}/seo', PatchEntitySeoController::class);
            Route::post('/entities/{uuid}/media', AttachEntityMediaController::class);
            Route::get('/entities/{uuid}/versions', ListEntityVersionsController::class);
            Route::get('/entities/{uuid}/versions/{version}', GetEntityVersionController::class);
            Route::post('/entities/{uuid}/versions/{version}/restore', RestoreEntityVersionController::class);
            Route::get('/entities/{uuid}/relationships', ListEntityRelationshipsController::class);
            Route::post('/entities/{uuid}/relationships', CreateEntityRelationshipController::class);
            Route::delete('/entities/{uuid}/relationships/{relationshipId}', DeleteEntityRelationshipController::class);
            Route::get('/entities/{uuid}/references', ListEntityReferencesController::class);
            Route::post('/entities/{uuid}/references', CreateEntityReferenceController::class);
            Route::delete('/entities/{uuid}/references/{referenceId}', DeleteEntityReferenceController::class);
            Route::patch('/entities/{uuid}', SaveEntityDraftController::class);
            Route::post('/entities/{uuid}/submit', SubmitEntityController::class);
            Route::post('/entities/{uuid}/publish', PublishEntityController::class);
            Route::post('/entities/{uuid}/unpublish', UnpublishEntityController::class);
            Route::post('/entities/{uuid}/archive', ArchiveEntityController::class);
            Route::post('/submissions/{id}/review', ReviewSubmissionController::class);
            Route::get('/ai-flags', [EditorialAiFlagsController::class, 'index']);
            Route::patch('/ai-flags/{id}', [EditorialAiFlagsController::class, 'update'])->whereNumber('id');
        });

        Route::prefix('admin')->middleware(['auth:sanctum', EnsureEmailIsVerified::class, EnsureAdminAccess::class])->group(function (): void {
            Route::get('/status', static fn () => response()->json(['data' => ['status' => 'admin-ready']]));
            Route::get('/users', ListAdminUsersController::class);
            Route::post('/users', CreateAdminUserController::class);
            Route::patch('/users/{uuid}', UpdateAdminUserController::class);
            Route::post('/users/{uuid}/roles', AssignUserRoleController::class);
            Route::delete('/users/{uuid}/roles/{role}', RemoveUserRoleController::class);
            Route::put('/users/{uuid}/domain-scopes', UpdateUserDomainScopesController::class);
            Route::get('/settings', GetAdminSettingsController::class);
            Route::patch('/settings', PatchAdminSettingsController::class);
            Route::patch('/feature-toggles', PatchFeatureTogglesController::class);
            Route::get('/audit-logs', ListAdminAuditLogsController::class);
            Route::get('/analytics/overview', AdminAnalyticsOverviewController::class);
            Route::get('/redirects', [AdminRedirectsController::class, 'index']);
            Route::post('/redirects', [AdminRedirectsController::class, 'store']);
            Route::delete('/redirects/{id}', [AdminRedirectsController::class, 'destroy'])->whereNumber('id');
        });

        Route::post('/analytics/views', RecordViewController::class);

        Route::post('/ai/chat', ChatController::class)->middleware('feature:ai_enabled');
        Route::post('/ai/messages/{uuid}/flag', FlagMessageController::class);

        Route::get('/sitemap.xml', [SitemapController::class, 'index']);
        Route::get('/sitemap-{locale}.xml', [SitemapController::class, 'locale'])
            ->where('locale', 'ar|en');
        Route::get('/robots.txt', [SitemapController::class, 'robots']);

        Route::middleware([LocaleMiddleware::class])->prefix('{locale}')->group(function (): void {
            Route::get('/status', static fn () => response()->json([
                'data' => ['locale' => app()->getLocale(), 'status' => 'ok'],
            ]));
            Route::get('/entities/{uuid}', GetPublicEntityController::class);
            Route::get('/entities/{uuid}/relationships', GetPublicEntityRelationshipsController::class);
            Route::get('/persons', ListPublicEntitiesController::class)->defaults('entityType', 'person');
            Route::get('/articles', ListPublicEntitiesController::class)->defaults('entityType', 'article');
            Route::get('/events', ListPublicEntitiesController::class)->defaults('entityType', 'event');
            Route::get('/places', ListPublicEntitiesController::class)->defaults('entityType', 'place');
            Route::get('/concepts', ListPublicEntitiesController::class)->defaults('entityType', 'concept');
            Route::get('/books', ListPublicEntitiesController::class)->defaults('entityType', 'book');
            Route::get('/sources', ListPublicEntitiesController::class)->defaults('entityType', 'source');
            Route::get('/persons/{uuid}', fn (string $locale, string $uuid) => app(GetPublicTypeEntityController::class)($locale, 'persons', $uuid));
            Route::get('/articles/{uuid}', fn (string $locale, string $uuid) => app(GetPublicTypeEntityController::class)($locale, 'articles', $uuid));
            Route::get('/events/{uuid}', fn (string $locale, string $uuid) => app(GetPublicTypeEntityController::class)($locale, 'events', $uuid));
            Route::get('/places/{uuid}', fn (string $locale, string $uuid) => app(GetPublicTypeEntityController::class)($locale, 'places', $uuid));
            Route::get('/concepts/{uuid}', fn (string $locale, string $uuid) => app(GetPublicTypeEntityController::class)($locale, 'concepts', $uuid));
            Route::get('/sources/{uuid}', fn (string $locale, string $uuid) => app(GetPublicTypeEntityController::class)($locale, 'sources', $uuid));
            Route::get('/books/{uuid}', fn (string $locale, string $uuid) => app(GetPublicTypeEntityController::class)($locale, 'books', $uuid));
            Route::get('/books/{uuid}/chapters', ListBookChaptersController::class);

            Route::get('/search', SearchController::class)->middleware('feature:search_enabled');
            Route::get('/search/suggestions', SearchSuggestionsController::class)->middleware('feature:search_enabled');
            Route::get('/search/related/{entityUuid}', SearchRelatedController::class)->middleware('feature:search_enabled');
            Route::get('/analytics/popular', PopularAnalyticsController::class);
            Route::get('/ai/recommendations', RecommendationsController::class);

            Route::prefix('quran')->group(function (): void {
                Route::get('/surahs', ListSurahsController::class);
                Route::get('/surahs/{number}', GetSurahController::class)->whereNumber('number');
                Route::get('/surahs/{number}/verses', ListSurahVersesController::class)->whereNumber('number');
                Route::get('/verses/{surah}:{ayah}', GetVerseController::class)->whereNumber(['surah', 'ayah']);
                Route::get('/translations', ListQuranTranslationsController::class);
                Route::get('/verses/{surah}:{ayah}/tafsir', ListVerseTafsirController::class)->whereNumber(['surah', 'ayah']);
                Route::get('/verses/{surah}:{ayah}/cross-references', GetVerseCrossReferencesController::class)->whereNumber(['surah', 'ayah']);
                Route::get('/topics', ListQuranTopicsController::class);
                Route::get('/search', QuranSearchController::class)->middleware('feature:search_enabled');
            });

            Route::prefix('ahlulbayt')->group(function (): void {
                Route::get('/', AhlulbaytLandingController::class);
                Route::get('/browse', AhlulbaytBrowseController::class);
                Route::get('/timeline', AhlulbaytTimelineController::class);
                Route::get('/persons/{uuid}/lineage', AhlulbaytLineageController::class);
                Route::get('/persons/{uuid}/genealogy', AhlulbaytGenealogyController::class);
            });
        });
    });
});
