<?php

declare(strict_types=1);

namespace App\Modules\Search\Application\Contracts;

interface SearchIndexGatewayInterface
{
    /**
     * @param  array<string, mixed>  $document
     */
    public function index(array $document): void;

    public function delete(string $meilisearchId): void;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{hits: list<array<string, mixed>>, total: int, facets: array<string, mixed>, took_ms: int}
     */
    public function search(string $query, array $filters, int $page, int $perPage): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function suggest(string $query, int $limit): array;
}
