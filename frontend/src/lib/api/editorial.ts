import { apiRequest } from '@/lib/api/client';
import type { ApiDataResponse } from '@/lib/api/endpoints';
import type {
  DashboardStats,
  EditorialEntity,
  EditorialEntityListItem,
  EditorialSubmission,
  EntityReference,
  EntityRelationship,
  EntitySeo,
  EntityVersion,
  MediaItem,
  PublishReadiness,
  WorkflowState,
} from '@/lib/cms/types';
import { getStoredToken } from '@/lib/auth/token';
import { API_BASE_URL } from '@/lib/constants';

export interface PaginatedMeta {
  pagination: {
    page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
}

export interface PaginatedResponse<T> extends ApiDataResponse<T[]> {
  meta?: PaginatedMeta;
}

function withLocale(locale?: string, params?: Record<string, string | number | undefined>) {
  const search = new URLSearchParams();
  if (locale) search.set('locale', locale);
  if (params) {
    for (const [key, value] of Object.entries(params)) {
      if (value !== undefined && value !== '') search.set(key, String(value));
    }
  }
  const query = search.toString();
  return query ? `?${query}` : '';
}

export function getEditorialDashboard(locale?: string) {
  return apiRequest<ApiDataResponse<DashboardStats>>(`/editorial/dashboard${withLocale(locale)}`, { locale });
}

export interface EditorialSettings {
  editor_domains: string[];
  supported_locales: string[];
  default_locale: string;
  maintenance_mode: boolean;
  block_types: string[];
}

export function getEditorialSettings() {
  return apiRequest<ApiDataResponse<EditorialSettings>>('/editorial/settings');
}

export function createEntityDraft(payload: {
  entity_type: string;
  domain: string;
  locale: string;
  title: string;
  slug?: string;
  summary?: string;
}) {
  return apiRequest<ApiDataResponse<EditorialEntity>>('/editorial/entities', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

export function getEditorialDrafts(locale?: string, page = 1) {
  return apiRequest<PaginatedResponse<EditorialEntityListItem>>(
    `/editorial/drafts${withLocale(locale, { page })}`,
    { locale },
  );
}

export function getEditorialSubmissions(locale?: string) {
  return apiRequest<ApiDataResponse<EditorialSubmission[]>>(`/editorial/submissions${withLocale(locale)}`, {
    locale,
  });
}

export function getEditorialEntities(
  locale?: string,
  filters?: {
    type?: string;
    workflow_state?: WorkflowState;
    q?: string;
    sort?: string;
    page?: number;
    per_page?: number;
  },
) {
  const params: Record<string, string | number | undefined> = {
    locale,
    page: filters?.page,
    per_page: filters?.per_page,
    sort: filters?.sort,
    'filter.type': filters?.type,
    'filter.workflow_state': filters?.workflow_state,
    'filter.q': filters?.q,
  };

  const search = new URLSearchParams();
  for (const [key, value] of Object.entries(params)) {
    if (value !== undefined && value !== '') search.set(key, String(value));
  }

  return apiRequest<PaginatedResponse<EditorialEntityListItem>>(
    `/editorial/entities?${search.toString()}`,
    { locale },
  );
}

export function getEditorialEntity(uuid: string, locale?: string) {
  return apiRequest<ApiDataResponse<EditorialEntity>>(
    `/editorial/entities/${uuid}${withLocale(locale)}`,
    { locale },
  );
}

export function saveEntityDraft(
  uuid: string,
  payload: {
    locale: string;
    title?: string;
    slug?: string;
    summary?: string;
    blocks?: unknown[];
    profile?: Record<string, unknown>;
  },
  locale?: string,
) {
  return apiRequest<ApiDataResponse<EditorialEntity>>(`/editorial/entities/${uuid}`, {
    method: 'PATCH',
    body: JSON.stringify(payload),
    locale,
  });
}

export function submitEntity(uuid: string, locale?: string) {
  return apiRequest<ApiDataResponse<unknown>>(`/editorial/entities/${uuid}/submit`, {
    method: 'POST',
    locale,
  });
}

export function publishEntity(uuid: string, locale?: string) {
  return apiRequest<ApiDataResponse<unknown>>(`/editorial/entities/${uuid}/publish`, {
    method: 'POST',
    locale,
  });
}

export function unpublishEntity(uuid: string, locale?: string) {
  return apiRequest<ApiDataResponse<unknown>>(`/editorial/entities/${uuid}/unpublish`, {
    method: 'POST',
    locale,
  });
}

export function getPublishReadiness(uuid: string, locale?: string) {
  return apiRequest<ApiDataResponse<PublishReadiness>>(
    `/editorial/entities/${uuid}/publish-readiness${withLocale(locale)}`,
    { locale },
  );
}

export function getEntityPreview(uuid: string, locale?: string) {
  return apiRequest<ApiDataResponse<Record<string, unknown>>>(
    `/editorial/entities/${uuid}/preview${withLocale(locale)}`,
    { locale },
  );
}

export function reviewSubmission(id: number, decision: 'approve' | 'reject', notes?: string, locale?: string) {
  return apiRequest<ApiDataResponse<unknown>>(`/editorial/submissions/${id}/review`, {
    method: 'POST',
    body: JSON.stringify({ decision, notes }),
    locale,
  });
}

export function searchEditorialEntities(query: string, locale?: string, page = 1) {
  return apiRequest<PaginatedResponse<EditorialEntityListItem>>(
    `/editorial/search/entities${withLocale(locale, { q: query, page })}`,
    { locale },
  );
}

export function getEntitySeo(uuid: string, locale?: string) {
  return apiRequest<ApiDataResponse<EntitySeo>>(
    `/editorial/entities/${uuid}/seo${withLocale(locale)}`,
    { locale },
  );
}

export function patchEntitySeo(
  uuid: string,
  payload: { locale: string; meta_title?: string; meta_description?: string },
  locale?: string,
) {
  return apiRequest<ApiDataResponse<EntitySeo>>(`/editorial/entities/${uuid}/seo`, {
    method: 'PATCH',
    body: JSON.stringify(payload),
    locale,
  });
}

export function getEntityVersions(uuid: string, locale?: string) {
  return apiRequest<ApiDataResponse<EntityVersion[]>>(
    `/editorial/entities/${uuid}/versions${withLocale(locale)}`,
    { locale },
  );
}

export function getEntityVersion(uuid: string, version: number, locale?: string) {
  return apiRequest<ApiDataResponse<{ version_number: number; snapshot: Record<string, unknown> }>>(
    `/editorial/entities/${uuid}/versions/${version}${withLocale(locale)}`,
    { locale },
  );
}

export function restoreEntityVersion(uuid: string, version: number, locale?: string) {
  return apiRequest<ApiDataResponse<EditorialEntity>>(
    `/editorial/entities/${uuid}/versions/${version}/restore`,
    { method: 'POST', locale },
  );
}

export function getEntityRelationships(uuid: string, locale?: string) {
  return apiRequest<ApiDataResponse<EntityRelationship[]>>(
    `/editorial/entities/${uuid}/relationships${withLocale(locale)}`,
    { locale },
  );
}

export function createEntityRelationship(
  uuid: string,
  payload: { target_entity_uuid: string; relationship_type: string; metadata?: Record<string, unknown> },
  locale?: string,
) {
  return apiRequest<ApiDataResponse<unknown>>(`/editorial/entities/${uuid}/relationships`, {
    method: 'POST',
    body: JSON.stringify(payload),
    locale,
  });
}

export function deleteEntityRelationship(uuid: string, relationshipId: number, locale?: string) {
  return apiRequest<void>(`/editorial/entities/${uuid}/relationships/${relationshipId}`, {
    method: 'DELETE',
    locale,
  });
}

export function getEntityReferences(uuid: string, locale?: string) {
  return apiRequest<ApiDataResponse<EntityReference[]>>(
    `/editorial/entities/${uuid}/references${withLocale(locale)}`,
    { locale },
  );
}

export function createEntityReference(
  uuid: string,
  payload: { source_entity_uuid: string; page_reference?: string; quote_excerpt?: string },
  locale?: string,
) {
  return apiRequest<ApiDataResponse<{ id: number }>>(`/editorial/entities/${uuid}/references`, {
    method: 'POST',
    body: JSON.stringify(payload),
    locale,
  });
}

export function deleteEntityReference(uuid: string, referenceId: number, locale?: string) {
  return apiRequest<void>(`/editorial/entities/${uuid}/references/${referenceId}`, {
    method: 'DELETE',
    locale,
  });
}

export function getEditorialMedia(
  locale?: string,
  filters?: { type?: string; status?: string; page?: number },
) {
  const search = new URLSearchParams();
  if (filters?.type) search.set('filter.type', filters.type);
  if (filters?.status) search.set('filter.status', filters.status);
  if (filters?.page) search.set('page', String(filters.page));
  const query = search.toString();

  return apiRequest<PaginatedResponse<MediaItem>>(`/editorial/media${query ? `?${query}` : ''}`, { locale });
}

export async function uploadEditorialMedia(file: File, altText?: string, locale?: string) {
  const formData = new FormData();
  formData.append('file', file);
  if (altText) formData.append('alt_text', altText);

  const token = getStoredToken();
  const response = await fetch(`${API_BASE_URL}/editorial/media`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      ...(locale ? { 'Accept-Language': locale } : {}),
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    credentials: 'include',
    body: formData,
  });

  if (!response.ok) {
    throw new Error('Upload failed');
  }

  return (await response.json()) as ApiDataResponse<MediaItem>;
}
