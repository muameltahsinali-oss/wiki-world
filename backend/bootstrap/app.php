<?php

use App\Modules\AI\Domain\Exceptions\AiRateLimitExceededException;
use App\Modules\AI\Domain\Exceptions\InsufficientCorpusException;
use App\Modules\Authentication\Domain\Exceptions\AccountLockedException;
use App\Modules\Authentication\Domain\Exceptions\InvalidCredentialsException;
use App\Modules\Authentication\Http\Middleware\EnsureEmailIsVerified;
use App\Modules\KnowledgeCore\Domain\Exceptions\DomainScopeDeniedException;
use App\Modules\KnowledgeCore\Domain\Exceptions\InvalidRelationshipException;
use App\Modules\KnowledgeCore\Domain\Exceptions\NonCreatableEntityTypeException;
use App\Modules\KnowledgeCore\Domain\Exceptions\SlugConflictException;
use App\Modules\Publishing\Domain\Exceptions\InvalidWorkflowTransitionException;
use App\Modules\Publishing\Domain\Exceptions\WorkflowConflictException;
use App\Modules\RolesPermissions\Http\Middleware\EnsureAdminAccess;
use App\Modules\RolesPermissions\Http\Middleware\EnsureEditorialAccess;
use App\Modules\Settings\Http\Middleware\EnsureFeatureEnabled;
use App\Modules\Shared\Http\Middleware\LocaleMiddleware;
use App\Modules\Shared\Http\Middleware\RequestIdMiddleware;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            RequestIdMiddleware::class,
            \App\Modules\Shared\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);
        $middleware->alias([
            'locale' => LocaleMiddleware::class,
            'verified.api' => EnsureEmailIsVerified::class,
            'editorial' => EnsureEditorialAccess::class,
            'admin' => EnsureAdminAccess::class,
            'feature' => EnsureFeatureEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (AccountLockedException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('ACCOUNT_LOCKED', __('errors.account_locked'), 423);
        });

        $exceptions->render(function (InvalidCredentialsException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('INVALID_CREDENTIALS', __('errors.invalid_credentials'), 401);
        });

        $exceptions->render(function (DomainScopeDeniedException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('DOMAIN_SCOPE_DENIED', __('errors.domain_scope_denied'), 403);
        });

        $exceptions->render(function (SlugConflictException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('SLUG_CONFLICT', __('errors.slug_conflict'), 409);
        });

        $exceptions->render(function (NonCreatableEntityTypeException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('NON_CREATABLE_ENTITY_TYPE', __('errors.non_creatable_entity_type'), 422);
        });

        $exceptions->render(function (WorkflowConflictException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('WORKFLOW_CONFLICT', __('errors.workflow_conflict'), 409);
        });

        $exceptions->render(function (InvalidWorkflowTransitionException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('INVALID_WORKFLOW_TRANSITION', __('errors.invalid_workflow_transition'), 409);
        });

        $exceptions->render(function (InvalidRelationshipException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('INVALID_RELATIONSHIP', __('errors.invalid_relationship'), 422);
        });

        $exceptions->render(function (InsufficientCorpusException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('INSUFFICIENT_CORPUS', $e->getMessage(), 422);
        });

        $exceptions->render(function (AiRateLimitExceededException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('RATE_LIMIT_EXCEEDED', $e->getMessage(), 429);
        });

        $exceptions->render(function (InvalidArgumentException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('INVALID_ARGUMENT', $e->getMessage(), 422);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error(
                'VALIDATION_FAILED',
                __('errors.validation_failed'),
                400,
                $e->errors(),
            );
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('NOT_FOUND', __('errors.not_found'), 404);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('NOT_FOUND', __('errors.not_found'), 404);
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = $e->getStatusCode();
            $code = match ($status) {
                401 => 'UNAUTHENTICATED',
                403 => 'FORBIDDEN',
                429 => 'RATE_LIMIT_EXCEEDED',
                default => 'HTTP_ERROR',
            };

            return ApiResponse::error($code, $e->getMessage() ?: __('errors.http_error'), $status);
        });
    })->create();
