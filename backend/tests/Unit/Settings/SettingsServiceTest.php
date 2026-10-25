<?php

declare(strict_types=1);

namespace Tests\Unit\Settings;

use App\Modules\Settings\Application\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_can_be_stored_and_retrieved(): void
    {
        $settings = $this->app->make(SettingsService::class);

        $settings->set('maintenance_mode', false, 'platform');

        $this->assertFalse($settings->get('maintenance_mode'));
        $this->assertDatabaseHas('site_settings', ['key' => 'maintenance_mode']);
    }
}
