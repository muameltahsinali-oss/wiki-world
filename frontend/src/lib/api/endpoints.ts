import { apiRequest } from '@/lib/api/client';
import { API_BASE_URL } from '@/lib/constants';

export interface ApiDataResponse<T> {
  data: T;
}

export interface HealthStatus {
  status: string;
}

export interface UserSummary {
  uuid: string;
  email: string;
  display_name: string | null;
  preferred_locale: string;
  email_verified?: boolean;
  roles: string[];
}

export interface UserProfile extends UserSummary {
  email_verified: boolean;
}

export interface LoginPayload {
  email: string;
  password: string;
  device_name?: string;
}

export interface LoginResponse {
  token: string;
  token_type: string;
  expires_at: string | null;
  user: UserSummary;
}

export interface RegisterPayload {
  email: string;
  password: string;
  password_confirmation: string;
  preferred_locale?: string;
  display_name?: string;
}

export interface RegisterResponse {
  message: string;
  user: UserSummary;
}

export interface UpdateMePayload {
  display_name?: string;
  preferred_locale?: string;
}

export function getHealth(locale?: string) {
  return apiRequest<ApiDataResponse<HealthStatus>>('/health', { locale });
}

export function login(payload: LoginPayload, locale?: string) {
  return apiRequest<ApiDataResponse<LoginResponse>>('/auth/login', {
    method: 'POST',
    body: JSON.stringify({
      ...payload,
      device_name: payload.device_name ?? 'web',
    }),
    locale,
  });
}

export function register(payload: RegisterPayload, locale?: string) {
  return apiRequest<ApiDataResponse<RegisterResponse>>('/auth/register', {
    method: 'POST',
    body: JSON.stringify(payload),
    locale,
  });
}

export function getMe(locale?: string) {
  return apiRequest<ApiDataResponse<UserProfile>>('/me', { locale });
}

export function updateMe(payload: UpdateMePayload, locale?: string) {
  return apiRequest<ApiDataResponse<UserProfile>>('/me', {
    method: 'PATCH',
    body: JSON.stringify(payload),
    locale,
  });
}

export function logout(locale?: string) {
  return apiRequest<void>('/auth/logout', { method: 'POST', locale });
}

export function forgotPassword(email: string, locale?: string) {
  return apiRequest<ApiDataResponse<{ message: string }>>('/auth/forgot-password', {
    method: 'POST',
    body: JSON.stringify({ email }),
    locale,
  });
}

export function resetPassword(
  payload: { email: string; password: string; password_confirmation: string; token: string },
  locale?: string,
) {
  return apiRequest<ApiDataResponse<{ message: string }>>('/auth/reset-password', {
    method: 'POST',
    body: JSON.stringify(payload),
    locale,
  });
}

export { API_BASE_URL };
