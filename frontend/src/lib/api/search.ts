import { apiRequest } from '@/lib/api/client';
import type { ApiDataResponse } from '@/lib/api/endpoints';
import type { EntityType } from '@/components/ui/badge';

export interface SearchHighlight {
  title?: string | null;
  summary?: string | null;
}

export interface SearchResultItem {
  uuid: string;
  type: EntityType;
  title: string;
  slug: string;
  summary: string | null;
  highlight?: SearchHighlight | null;
  score?: number;
  module?: string | null;
}

export interface SearchFacetBucket {
  value: string;
  count: number;
}

export interface SearchFilters {
  type?: string;
  module?: string;
  date_from?: string;
  date_to?: string;
  page?: number;
  per_page?: number;
}

export interface SearchResponse {
  data: SearchResultItem[];
  meta: {
    query: string;
    total: number;
    took_ms?: number;
    pagination: {
      page: number;
      per_page: number;
      last_page: number;
      total?: number;
    };
    facets?: {
      type?: SearchFacetBucket[];
      module?: SearchFacetBucket[];
    };
    suggestions?: string[];
  };
}

export interface SearchSuggestion {
  text: string;
  type: EntityType;
  uuid: string;
  slug?: string;
}

export interface RelatedSearchItem {
  uuid: string;
  type: EntityType;
  title: string;
  slug?: string;
  summary?: string | null;
}

function buildQuery(params: Record<string, string | number | undefined>): string {
  const search = new URLSearchParams();
  for (const [key, value] of Object.entries(params)) {
    if (value !== undefined && value !== '') search.set(key, String(value));
  }
  const query = search.toString();
  return query ? `?${query}` : '';
}

export function search(q: string, filters: SearchFilters = {}, locale: string) {
  return apiRequest<SearchResponse>(
    `/${locale}/search${buildQuery({
      q,
      'filter[type]': filters.type,
      'filter[module]': filters.module,
      'filter[date_from]': filters.date_from,
      'filter[date_to]': filters.date_to,
      page: filters.page,
      per_page: filters.per_page,
    })}`,
    { locale },
  );
}

export function suggestions(q: string, locale: string, limit = 8) {
  return apiRequest<ApiDataResponse<SearchSuggestion[]>>(
    `/${locale}/search/suggestions${buildQuery({ q, limit })}`,
    { locale },
  );
}

export function related(uuid: string, locale: string, limit = 10) {
  return apiRequest<ApiDataResponse<RelatedSearchItem[]>>(
    `/${locale}/search/related/${uuid}${buildQuery({ limit })}`,
    { locale },
  );
}
