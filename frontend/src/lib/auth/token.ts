const TOKEN_KEY = 'wikiworld-token';

export function getStoredToken(): string | null {
  if (typeof window === 'undefined') return null;
  return localStorage.getItem(TOKEN_KEY);
}

export function setStoredToken(token: string | null): void {
  if (typeof window === 'undefined') return;
  if (token) {
    localStorage.setItem(TOKEN_KEY, token);
  } else {
    localStorage.removeItem(TOKEN_KEY);
  }
}

export const EDITORIAL_ROLES = [
  'editor',
  'reviewer',
  'senior_editor',
  'admin',
  'super_admin',
] as const;

export function hasEditorialRole(roles: string[]): boolean {
  return roles.some((role) => (EDITORIAL_ROLES as readonly string[]).includes(role));
}

export const ADMIN_ROLES = ['admin', 'super_admin'] as const;

export function hasAdminRole(roles: string[]): boolean {
  return roles.some((role) => (ADMIN_ROLES as readonly string[]).includes(role));
}
