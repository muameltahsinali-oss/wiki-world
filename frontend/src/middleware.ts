import createMiddleware from 'next-intl/middleware';
import { type NextRequest, NextResponse } from 'next/server';
import { routing } from './i18n/routing';

const intlMiddleware = createMiddleware(routing);

const API_BASE = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8080/api/v1';

async function isMaintenanceMode(): Promise<boolean> {
  try {
    const controller = new AbortController();
    const id = setTimeout(() => controller.abort(), 2000);
    const response = await fetch(`${API_BASE}/site/config`, {
      next: { revalidate: 30 },
      signal: controller.signal,
    });
    clearTimeout(id);
    if (!response.ok) return false;
    const payload = (await response.json()) as { data?: { maintenance_mode?: boolean } };
    return Boolean(payload.data?.maintenance_mode);
  } catch {
    return false;
  }
}

export default async function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const exempt =
    pathname.includes('/maintenance') ||
    pathname.includes('/cms') ||
    pathname.includes('/admin') ||
    pathname.includes('/login') ||
    pathname.includes('/api/');

  if (!exempt && (await isMaintenanceMode())) {
    const locale = pathname.startsWith('/en') ? 'en' : 'ar';
    return NextResponse.redirect(new URL(`/${locale}/maintenance`, request.url));
  }

  return intlMiddleware(request);
}

export const config = {
  matcher: ['/', '/(ar|en)/:path*'],
};
