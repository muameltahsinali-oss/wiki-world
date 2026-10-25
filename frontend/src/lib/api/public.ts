import { apiRequest } from '@/lib/api/client';
import type { ApiDataResponse } from '@/lib/api/endpoints';
import type { PaginatedMeta, PaginatedResponse } from '@/lib/api/editorial';
import type { EntityType } from '@/components/ui/badge';
import {
  ENTITY_TYPE_ROUTES,
  type Bookmark,
  type PublicEntity,
  type PublicEntityListItem,
  type PublicRelationship,
  type ReadingHistoryEntry,
} from '@/lib/entity/types';

function buildQuery(params: Record<string, string | number | undefined>): string {
  const search = new URLSearchParams();
  for (const [key, value] of Object.entries(params)) {
    if (value !== undefined && value !== '') search.set(key, String(value));
  }
  const query = search.toString();
  return query ? `?${query}` : '';
}

export function getPublicEntity(locale: string, uuid: string) {
  return apiRequest<ApiDataResponse<PublicEntity>>(`/${locale}/entities/${uuid}`, { locale });
}

export function getPublicTypeEntity(locale: string, typeRoute: string, uuid: string) {
  return apiRequest<ApiDataResponse<PublicEntity>>(`/${locale}/${typeRoute}/${uuid}`, { locale });
}

export function getPublicEntityByType(locale: string, type: EntityType, uuid: string) {
  return getPublicTypeEntity(locale, ENTITY_TYPE_ROUTES[type], uuid);
}

export function getPublicEntities(
  locale: string,
  typeRoute: string,
  filters?: {
    page?: number;
    per_page?: number;
    q?: string;
    sort?: string;
    module?: string;
    era?: string;
    is_masum?: boolean;
    generation?: number;
  },
) {
  const params: Record<string, string | number | undefined> = {
    page: filters?.page,
    per_page: filters?.per_page,
    q: filters?.q,
    sort: filters?.sort,
    'filter[module]': filters?.module,
    'filter[era]': filters?.era,
    'filter[is_masum]': filters?.is_masum === undefined ? undefined : filters.is_masum ? '1' : '0',
    'filter[generation]': filters?.generation,
  };

  return apiRequest<PaginatedResponse<PublicEntityListItem>>(
    `/${locale}/${typeRoute}${buildQuery(params)}`,
    { locale },
  );
}

export function getPublicRelationships(locale: string, uuid: string) {
  return apiRequest<ApiDataResponse<PublicRelationship[]>>(
    `/${locale}/entities/${uuid}/relationships`,
    { locale },
  );
}

export function getBookmarks(locale?: string) {
  return apiRequest<ApiDataResponse<Bookmark[]>>('/me/bookmarks', { locale });
}

export function addBookmark(entityUuid: string, locale?: string) {
  return apiRequest<ApiDataResponse<Bookmark>>('/me/bookmarks', {
    method: 'POST',
    body: JSON.stringify({ entity_uuid: entityUuid }),
    locale,
  });
}

export function removeBookmark(entityUuid: string, locale?: string) {
  return apiRequest<void>(`/me/bookmarks/${entityUuid}`, {
    method: 'DELETE',
    locale,
  });
}

export function getReadingHistory(locale?: string) {
  return apiRequest<ApiDataResponse<ReadingHistoryEntry[]>>('/me/history', { locale });
}

export function recordReadingHistory(entityUuid: string, locale?: string) {
  return apiRequest<ApiDataResponse<ReadingHistoryEntry>>('/me/history', {
    method: 'POST',
    body: JSON.stringify({ entity_uuid: entityUuid }),
    locale,
  });
}

export function clearReadingHistory(locale?: string) {
  return apiRequest<void>('/me/history', {
    method: 'DELETE',
    locale,
  });
}

export type { PaginatedMeta };
