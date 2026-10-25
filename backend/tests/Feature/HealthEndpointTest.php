<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class HealthEndpointTest extends TestCase
{
    public function test_health_returns_ok(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertOk()
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.service', 'wikiworld-api');
    }

    public function test_health_ready_returns_checks(): void
    {
        $response = $this->getJson('/api/health/ready');

        $response->assertJsonStructure([
            'data' => [
                'status',
                'checks' => [
                    'database',
                    'redis',
                    'queue',
                    'storage',
                    'search',
                    'ai',
                ],
            ],
        ]);
    }
}
