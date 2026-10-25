<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success(mixed $data = null, array $meta = [], int $status = 200): JsonResponse
    {
        $payload = ['data' => $data];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        if (isset($meta['request_id'])) {
            // request_id always in meta
        } elseif (request()->attributes->has('request_id')) {
            $payload['meta'] = array_merge($meta, [
                'request_id' => request()->attributes->get('request_id'),
            ]);
        }

        return response()->json($payload, $status);
    }

    public static function error(
        string $code,
        string $message,
        int $status = 400,
        array $details = [],
    ): JsonResponse {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details ?: (object) [],
            ],
            'meta' => [
                'request_id' => request()->attributes->get('request_id'),
                'timestamp' => now()->toIso8601String(),
            ],
        ], $status);
    }

    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
