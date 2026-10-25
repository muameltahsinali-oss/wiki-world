<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Modules\Settings\Application\Services\FeatureToggleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class FeatureToggleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedDefaults();
    }
    public function test_registration_disabled_returns_503(): void
    {
        $this->setToggle('registration_enabled', false);

        $this->postJson('/api/v1/auth/register', [
            'email' => 'blocked@wikiworld.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])
            ->assertStatus(503)
            ->assertJsonPath('error.code', 'FEATURE_DISABLED');
    }

    public function test_search_disabled_returns_503(): void
    {
        $this->setToggle('search_enabled', false);

        $this->getJson('/api/v1/ar/search?q=test')
            ->assertStatus(503)
            ->assertJsonPath('error.code', 'FEATURE_DISABLED');
    }

    private function seedDefaults(): void
    {
        $service = $this->app->make(FeatureToggleService::class);
        $service->all();
    }

    private function setToggle(string $key, bool $enabled): void
    {
        DB::table('feature_toggles')->updateOrInsert(
            ['key' => $key],
            ['is_enabled' => $enabled, 'updated_at' => now()],
        );
    }
}
