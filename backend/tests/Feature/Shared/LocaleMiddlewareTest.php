<?php

declare(strict_types=1);

namespace Tests\Feature\Shared;

use Tests\TestCase;

final class LocaleMiddlewareTest extends TestCase
{
    public function test_invalid_locale_returns_400(): void
    {
        $response = $this->getJson('/api/v1/fr/status');

        $response->assertStatus(400)
            ->assertJsonPath('error.code', 'INVALID_LOCALE');
    }

    public function test_valid_locale_sets_content_language_header(): void
    {
        $response = $this->getJson('/api/v1/ar/status');

        $response->assertOk()
            ->assertJsonPath('data.locale', 'ar');
        $this->assertSame('ar', $response->headers->get('Content-Language'));
    }
}
