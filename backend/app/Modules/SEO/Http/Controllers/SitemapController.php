<?php

declare(strict_types=1);

namespace App\Modules\SEO\Http\Controllers;

use App\Modules\SEO\Application\Services\SitemapService;
use Illuminate\Http\Response;

final class SitemapController
{
    public function __construct(
        private readonly SitemapService $sitemap,
    ) {}

    public function index(): Response
    {
        return response($this->sitemap->renderIndexXml(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function locale(string $locale): Response
    {
        return response($this->sitemap->renderLocaleXml($locale), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function robots(): Response
    {
        return response($this->sitemap->robotsTxt(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
