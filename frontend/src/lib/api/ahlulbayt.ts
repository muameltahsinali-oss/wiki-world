import { apiRequest } from '@/lib/api/client';
import type { ApiDataResponse } from '@/lib/api/endpoints';
import type { PaginatedResponse } from '@/lib/api/editorial';
import type { EntityType } from '@/components/ui/badge';
import type { PublicEntityBlock, PublicEntityListItem } from '@/lib/entity/types';

export interface AhlulbaytLanding {
  title: string;
  intro_blocks?: PublicEntityBlock[];
  featured_persons: PublicEntityListItem[];
  featured_events: PublicEntityListItem[];
  timeline_preview?: TimelineEvent[];
  collections?: Array<{ slug: string; title: string }>;
}

export interface GenealogyNode {
  uuid: string;
  title: string;
  entity_type: EntityType;
  is_masum?: boolean;
  imam_number?: number | null;
  children?: GenealogyNode[];
}

export interface LineagePerson {
  uuid: string;
  title: string;
  relationship: string;
  generation?: number;
}

export interface LineageData {
  ancestors: LineagePerson[];
  descendants: LineagePerson[];
}

export interface TimelineEvent {
  uuid: string;
  type: EntityType;
  title: string;
  start_date_hijri?: string | null;
  end_date_hijri?: string | null;
  era?: string | null;
  place?: { uuid: string; title: string } | null;
  summary?: string | null;
}

export interface TimelineResponse {
  data: TimelineEvent[];
  meta?: {
    pagination?: {
      next_cursor?: string | null;
      has_more?: boolean;
    };
  };
}

function buildQuery(params: Record<string, string | number | undefined>): string {
  const search = new URLSearchParams();
  for (const [key, value] of Object.entries(params)) {
    if (value !== undefined && value !== '') search.set(key, String(value));
  }
  const query = search.toString();
  return query ? `?${query}` : '';
}

export function getAhlulbaytLanding(locale: string) {
  return apiRequest<ApiDataResponse<AhlulbaytLanding>>(`/${locale}/ahlulbayt`, { locale });
}

export function getAhlulbaytBrowse(
  locale: string,
  filters?: {
    type?: string;
    generation?: number;
    is_masum?: boolean;
    era?: string;
    q?: string;
    sort?: string;
    page?: number;
    per_page?: number;
  },
) {
  const params: Record<string, string | number | undefined> = {
    page: filters?.page,
    per_page: filters?.per_page,
    q: filters?.q,
    sort: filters?.sort,
    'filter[type]': filters?.type,
    'filter[generation]': filters?.generation,
    'filter[is_masum]': filters?.is_masum === undefined ? undefined : filters.is_masum ? '1' : '0',
    'filter[era]': filters?.era,
  };

  return apiRequest<PaginatedResponse<PublicEntityListItem>>(
    `/${locale}/ahlulbayt/browse${buildQuery(params)}`,
    { locale },
  );
}

export function getGenealogy(locale: string) {
  return apiRequest<ApiDataResponse<GenealogyNode>>(`/${locale}/ahlulbayt/genealogy`, { locale });
}

export function getPersonLineage(locale: string, uuid: string) {
  return apiRequest<ApiDataResponse<LineageData>>(
    `/${locale}/ahlulbayt/persons/${uuid}/lineage`,
    { locale },
  );
}

export function getAhlulbaytTimeline(
  locale: string,
  filters?: {
    era?: string;
    person?: string;
    module?: string;
    cursor?: string;
    limit?: number;
  },
) {
  const params: Record<string, string | number | undefined> = {
    'filter[era]': filters?.era,
    'filter[person]': filters?.person,
    'filter[module]': filters?.module ?? 'ahlulbayt',
    cursor: filters?.cursor,
    limit: filters?.limit,
  };

  return apiRequest<TimelineResponse>(`/${locale}/timeline${buildQuery(params)}`, { locale });
}
