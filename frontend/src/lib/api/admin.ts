import { apiRequest } from '@/lib/api/client';
import type { ApiDataResponse } from '@/lib/api/endpoints';
import type { PaginatedResponse } from '@/lib/api/editorial';

export interface AdminUser {
  uuid: string;
  email: string;
  display_name: string | null;
  preferred_locale: string | null;
  is_active: boolean;
  roles: string[];
  domain_scopes: string[];
}

export interface AdminSettings {
  maintenance_mode: boolean;
  site_name: string;
  default_locale: string;
  feature_toggles: Array<{ key: string; is_enabled: boolean; updated_at: string }>;
}

export interface AuditLogEntry {
  id: number;
  action: string;
  subject_type: string;
  subject_id: number | null;
  diff: Record<string, unknown> | null;
  ip_address: string | null;
  created_at: string;
  actor: { uuid: string; email: string } | null;
}

export interface AnalyticsOverview {
  period_days: number;
  total_page_views: number;
  unique_sessions: number;
  top_entities: Array<{ uuid: string; view_count: number }>;
}

export function getAdminUsers(page = 1, q?: string) {
  const params = new URLSearchParams({ page: String(page) });
  if (q) params.set('q', q);
  return apiRequest<PaginatedResponse<AdminUser>>(`/admin/users?${params.toString()}`);
}

export function createAdminUser(payload: {
  email: string;
  password: string;
  display_name?: string;
  role?: string;
}) {
  return apiRequest<ApiDataResponse<AdminUser>>('/admin/users', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

export function assignAdminUserRole(uuid: string, role: string) {
  return apiRequest<ApiDataResponse<AdminUser>>(`/admin/users/${uuid}/roles`, {
    method: 'POST',
    body: JSON.stringify({ role }),
  });
}

export function removeAdminUserRole(uuid: string, role: string) {
  return apiRequest<ApiDataResponse<AdminUser>>(`/admin/users/${uuid}/roles/${role}`, {
    method: 'DELETE',
  });
}

export function getAdminSettings() {
  return apiRequest<ApiDataResponse<AdminSettings>>('/admin/settings');
}

export function patchAdminSettings(payload: Partial<Pick<AdminSettings, 'maintenance_mode' | 'site_name' | 'default_locale'>>) {
  return apiRequest<ApiDataResponse<AdminSettings>>('/admin/settings', {
    method: 'PATCH',
    body: JSON.stringify(payload),
  });
}

export function patchFeatureToggles(toggles: Record<string, boolean>) {
  return apiRequest<ApiDataResponse<AdminSettings['feature_toggles']>>('/admin/feature-toggles', {
    method: 'PATCH',
    body: JSON.stringify({ toggles }),
  });
}

export function getAdminAuditLogs(page = 1, action?: string) {
  const params = new URLSearchParams({ page: String(page) });
  if (action) params.set('filter[action]', action);
  return apiRequest<PaginatedResponse<AuditLogEntry>>(`/admin/audit-logs?${params.toString()}`);
}

export function getAdminAnalyticsOverview(days = 30) {
  return apiRequest<ApiDataResponse<AnalyticsOverview>>(`/admin/analytics/overview?days=${days}`);
}
