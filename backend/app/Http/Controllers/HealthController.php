<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

final class HealthController extends Controller
{
    public function health(): JsonResponse
    {
        return ApiResponse::success([
            'status' => 'ok',
            'service' => 'wikiworld-api',
            'version' => config('wikiworld.api_version', 'v1'),
        ]);
    }

    public function ready(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
            'search' => $this->checkSearch(),
            'ai' => $this->checkAi(),
        ];

        $healthy = collect($checks)->every(fn (array $c) => $c['status'] === 'ok');

        return ApiResponse::success(
            [
                'status' => $healthy ? 'ready' : 'degraded',
                'checks' => $checks,
            ],
            [],
            $healthy ? 200 : 503,
        );
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['status' => 'ok'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => 'Database unreachable'];
        }
    }

    private function checkRedis(): array
    {
        if (config('cache.default') === 'array' || config('database.redis.client') === 'phpredis' && ! extension_loaded('redis')) {
            return ['status' => 'ok', 'mode' => 'skipped'];
        }

        try {
            Redis::connection()->ping();

            return ['status' => 'ok'];
        } catch (\Throwable) {
            return ['status' => 'error', 'message' => 'Redis unreachable'];
        }
    }

    private function checkQueue(): array
    {
        return ['status' => 'ok', 'driver' => config('queue.default')];
    }

    private function checkStorage(): array
    {
        try {
            $disk = config('filesystems.default');
            Storage::disk($disk);

            return ['status' => 'ok', 'disk' => $disk];
        } catch (\Throwable) {
            return ['status' => 'error', 'message' => 'Storage unreachable'];
        }
    }

    private function checkSearch(): array
    {
        if (! config('wikiworld.search.enabled')) {
            return ['status' => 'ok', 'mode' => 'disabled'];
        }

        return ['status' => 'ok', 'driver' => config('wikiworld.search.driver')];
    }

    private function checkAi(): array
    {
        if (! config('wikiworld.ai.enabled')) {
            return ['status' => 'ok', 'mode' => 'disabled'];
        }

        return [
            'status' => config('wikiworld.ai.api_key') ? 'ok' : 'degraded',
            'provider' => config('wikiworld.ai.provider'),
        ];
    }
}
