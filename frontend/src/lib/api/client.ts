import { getStoredToken } from '@/lib/auth/token';
import { API_BASE_URL } from '@/lib/constants';
import { z } from 'zod';

export const apiErrorSchema = z.object({
  error: z.object({
    code: z.string(),
    message: z.string(),
    details: z.record(z.string(), z.array(z.string())).or(z.object({})).optional(),
  }),
  meta: z.object({
    request_id: z.string().nullable().optional(),
    timestamp: z.string().optional(),
  }),
});

export type ApiError = z.infer<typeof apiErrorSchema>;

export class ApiClientError extends Error {
  constructor(
    public readonly code: string,
    message: string,
    public readonly status: number,
    public readonly details?: Record<string, string[]>,
    public readonly requestId?: string | null,
  ) {
    super(message);
    this.name = 'ApiClientError';
  }
}

export interface RequestOptions extends RequestInit {
  locale?: string;
  retry?: number;
  signal?: AbortSignal;
}

export async function apiRequest<T>(
  path: string,
  options: RequestOptions = {},
): Promise<T> {
  const { locale, retry = 0, headers, ...init } = options;
  const baseUrl = path.startsWith('http') ? '' : API_BASE_URL;
  const url = path.startsWith('http') ? path : `${baseUrl}${path}`;
  const token = getStoredToken();

  const response = await fetch(url, {
    ...init,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(locale ? { 'Accept-Language': locale } : {}),
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...headers,
    },
    signal: options.signal,
  });

  if (!response.ok) {
    let payload: unknown;
    try {
      payload = await response.json();
    } catch {
      throw new ApiClientError('HTTP_ERROR', response.statusText, response.status);
    }

    const parsed = apiErrorSchema.safeParse(payload);
    if (parsed.success) {
      throw new ApiClientError(
        parsed.data.error.code,
        parsed.data.error.message,
        response.status,
        parsed.data.error.details as Record<string, string[]> | undefined,
        parsed.data.meta.request_id,
      );
    }

    throw new ApiClientError('HTTP_ERROR', 'Request failed', response.status);
  }

  if (response.status === 204) {
    return undefined as T;
  }

  return (await response.json()) as T;
}

export async function apiRequestWithRetry<T>(
  path: string,
  options: RequestOptions = {},
): Promise<T> {
  const maxRetries = options.retry ?? 0;
  let attempt = 0;

  while (true) {
    try {
      return await apiRequest<T>(path, options);
    } catch (error) {
      if (
        attempt >= maxRetries ||
        !(error instanceof ApiClientError) ||
        error.status < 500
      ) {
        throw error;
      }
      attempt += 1;
      await new Promise((r) => setTimeout(r, 300 * attempt));
    }
  }
}
