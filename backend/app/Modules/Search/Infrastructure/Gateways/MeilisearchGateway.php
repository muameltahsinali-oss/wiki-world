<?php

declare(strict_types=1);

namespace App\Modules\Search\Infrastructure\Gateways;

use App\Modules\Search\Application\Contracts\SearchIndexGatewayInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class MeilisearchGateway implements SearchIndexGatewayInterface
{
    private const INDEX = 'entities';

    public function index(array $document): void
    {
        try {
            $this->client()
                ->post($this->url('/indexes/'.self::INDEX.'/documents'), [$document])
                ->throw();
        } catch (Throwable $e) {
            Log::warning('Meilisearch index failed: '.$e->getMessage());
            throw new RuntimeException('Meilisearch index failed.', 0, $e);
        }
    }

    public function delete(string $meilisearchId): void
    {
        try {
            $this->client()
                ->delete($this->url('/indexes/'.self::INDEX.'/documents/'.$meilisearchId))
                ->throw();
        } catch (Throwable $e) {
            Log::warning('Meilisearch delete failed: '.$e->getMessage());
            throw new RuntimeException('Meilisearch delete failed.', 0, $e);
        }
    }

    public function search(string $query, array $filters, int $page, int $perPage): array
    {
        $started = hrtime(true);

        try {
            $filterExpressions = [];
            if (isset($filters['type']) && $filters['type'] !== '') {
                $types = array_map('trim', explode(',', (string) $filters['type']));
                $quoted = implode(', ', array_map(static fn (string $t): string => '"'.$t.'"', $types));
                $filterExpressions[] = 'entity_type_code IN ['.$quoted.']';
            }
            if (isset($filters['module']) && is_string($filters['module']) && $filters['module'] !== '') {
                $filterExpressions[] = 'module = "'.$filters['module'].'"';
            }
            if (isset($filters['locale']) && is_string($filters['locale'])) {
                $filterExpressions[] = 'locale = "'.$filters['locale'].'"';
            }

            $payload = [
                'q' => $query,
                'limit' => $perPage,
                'offset' => max(0, ($page - 1) * $perPage),
                'facets' => ['entity_type_code', 'module'],
            ];

            if ($filterExpressions !== []) {
                $payload['filter'] = implode(' AND ', $filterExpressions);
            }

            $response = $this->client()
                ->post($this->url('/indexes/'.self::INDEX.'/search'), $payload)
                ->throw()
                ->json();

            $hits = is_array($response['hits'] ?? null) ? $response['hits'] : [];
            $facetDistribution = is_array($response['facetDistribution'] ?? null) ? $response['facetDistribution'] : [];

            return [
                'hits' => $hits,
                'total' => (int) ($response['estimatedTotalHits'] ?? count($hits)),
                'facets' => [
                    'type' => $this->mapFacet($facetDistribution['entity_type_code'] ?? []),
                    'module' => $this->mapFacet($facetDistribution['module'] ?? []),
                ],
                'took_ms' => (int) ((hrtime(true) - $started) / 1_000_000),
            ];
        } catch (ConnectionException|Throwable $e) {
            Log::warning('Meilisearch search failed, caller should fall back: '.$e->getMessage());
            throw new RuntimeException('Meilisearch search failed.', 0, $e);
        }
    }

    public function suggest(string $query, int $limit): array
    {
        $result = $this->search($query, [], 1, $limit);

        return array_map(static fn (array $hit): array => [
            'text' => (string) ($hit['title'] ?? ''),
            'type' => (string) ($hit['entity_type_code'] ?? ''),
            'entity_id' => $hit['entity_id'] ?? null,
            'meilisearch_id' => $hit['id'] ?? ($hit['meilisearch_id'] ?? null),
            'module_flags' => $hit['module_flags'] ?? [],
        ], $result['hits']);
    }

    private function client(): PendingRequest
    {
        $key = config('wikiworld.search.meilisearch.key');

        $request = Http::timeout(5)->acceptJson();

        if (is_string($key) && $key !== '') {
            $request = $request->withToken($key);
        }

        return $request;
    }

    private function url(string $path): string
    {
        $host = rtrim((string) config('wikiworld.search.meilisearch.host', 'http://localhost:7700'), '/');

        return $host.$path;
    }

    /**
     * @param  array<string, int|string>  $distribution
     * @return list<array{value: string, count: int}>
     */
    private function mapFacet(array $distribution): array
    {
        $out = [];
        foreach ($distribution as $value => $count) {
            $out[] = ['value' => (string) $value, 'count' => (int) $count];
        }

        return $out;
    }
}
