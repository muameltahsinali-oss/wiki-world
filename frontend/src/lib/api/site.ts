import { apiRequest } from '@/lib/api/client';
import type { ApiDataResponse } from '@/lib/api/endpoints';

export interface SiteConfig {
  maintenance_mode: boolean;
  registration_enabled: boolean;
  search_enabled: boolean;
  ai_enabled: boolean;
}

export function getSiteConfig() {
  return apiRequest<ApiDataResponse<SiteConfig>>('/site/config');
}
