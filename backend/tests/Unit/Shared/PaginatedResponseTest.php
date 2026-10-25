<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Modules\Shared\Support\PaginatedResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

final class PaginatedResponseTest extends TestCase
{
    public function test_paginated_response_matches_api_spec_envelope(): void
    {
        $paginator = new LengthAwarePaginator(
            items: [['id' => 1]],
            total: 1,
            perPage: 20,
            currentPage: 1,
        );

        $response = PaginatedResponse::fromPaginator($paginator);
        $payload = $response->getData(true);

        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('meta', $payload);
        $this->assertSame(1, $payload['meta']['pagination']['page']);
        $this->assertSame(20, $payload['meta']['pagination']['per_page']);
    }
}
