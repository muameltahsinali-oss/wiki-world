<?php

declare(strict_types=1);

namespace App\Modules\Shared\Http\Middleware;

use App\Modules\Shared\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class LocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if ($locale === null) {
            return $next($request);
        }

        $supported = config('wikiworld.supported_locales', ['ar', 'en']);

        if (! in_array($locale, $supported, true)) {
            return ApiResponse::error('INVALID_LOCALE', __('errors.invalid_locale'), 400);
        }

        app()->setLocale($locale);

        $response = $next($request);
        $response->headers->set('Content-Language', $locale);

        return $response;
    }
}
