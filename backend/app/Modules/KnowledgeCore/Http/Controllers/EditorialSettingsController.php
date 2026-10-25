<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Controllers;

use App\Modules\Settings\Application\Services\SettingsService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class EditorialSettingsController
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    public function __invoke(): JsonResponse
    {
        return ApiResponse::success([
            'editor_domains' => config('wikiworld.editor_domains', []),
            'supported_locales' => config('wikiworld.supported_locales', []),
            'default_locale' => config('wikiworld.default_locale', 'ar'),
            'maintenance_mode' => (bool) $this->settings->get('maintenance_mode', false),
            'block_types' => ['paragraph', 'heading', 'quote', 'list', 'citation', 'entity_link', 'image'],
        ]);
    }
}
