<?php

declare(strict_types=1);

namespace App\Modules\AI\Providers;

use App\Modules\AI\Application\Contracts\EmbeddingGatewayInterface;
use App\Modules\AI\Application\Contracts\LLMProviderGatewayInterface;
use App\Modules\AI\Application\Services\AiChatService;
use App\Modules\AI\Application\Services\CorpusRetrievalService;
use App\Modules\AI\Application\Services\RagPipelineService;
use App\Modules\AI\Application\Services\RefusalPolicyHandler;
use App\Modules\AI\Infrastructure\Gateways\FakeLLMGateway;
use App\Modules\AI\Infrastructure\Gateways\OpenAIGateway;
use App\Modules\AI\Infrastructure\Gateways\StubEmbeddingGateway;
use App\Modules\Shared\Providers\ModuleServiceProvider;

final class AIServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'AI';
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(CorpusRetrievalService::class);
        $this->app->singleton(RefusalPolicyHandler::class);
        $this->app->singleton(FakeLLMGateway::class);
        $this->app->singleton(OpenAIGateway::class);
        $this->app->singleton(StubEmbeddingGateway::class);

        $this->app->singleton(LLMProviderGatewayInterface::class, function ($app) {
            $provider = (string) config('wikiworld.ai.provider', 'openai');
            $env = (string) config('app.env');

            if ($provider === 'fake' || $env === 'testing') {
                return $app->make(FakeLLMGateway::class);
            }

            return $app->make(OpenAIGateway::class);
        });

        $this->app->singleton(EmbeddingGatewayInterface::class, StubEmbeddingGateway::class);
        $this->app->singleton(RagPipelineService::class);
        $this->app->singleton(AiChatService::class);
    }

    protected function bootModule(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
