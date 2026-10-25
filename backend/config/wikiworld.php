<?php

declare(strict_types=1);

return [

    'name' => env('APP_NAME', 'Wiki World'),
    'api_version' => 'v1',

    'supported_locales' => ['ar', 'en'],
    'default_locale' => 'ar',
    'fallback_locale' => 'ar',

    'search' => [
        'enabled' => env('SEARCH_ENABLED', false),
        'driver' => env('SEARCH_DRIVER', 'database'),
        'meilisearch' => [
            'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
            'key' => env('MEILISEARCH_KEY'),
        ],
    ],

    'ai' => [
        'enabled' => env('AI_ENABLED', false),
        'provider' => env('AI_PROVIDER', 'openai'),
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o'),
        'rate_limit' => [
            'guest' => (int) env('AI_RATE_LIMIT_GUEST', 10),
            'registered' => (int) env('AI_RATE_LIMIT_REGISTERED', 50),
        ],
    ],

    'roles' => [
        'registered',
        'editor',
        'reviewer',
        'senior_editor',
        'admin',
        'super_admin',
    ],

    'editor_domains' => [
        'quran',
        'ahlulbayt',
        'general',
        'books_sources',
    ],

    'media' => [
        'cdn_url' => env('MEDIA_CDN_URL', env('AWS_URL')),
    ],

    'frontend' => [
        'url' => env('FRONTEND_URL', 'http://localhost:3000'),
        'revalidate_secret' => env('REVALIDATE_SECRET'),
    ],

];
