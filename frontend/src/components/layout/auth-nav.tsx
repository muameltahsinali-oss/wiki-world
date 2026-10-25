'use client';

import { useQuery, useQueryClient } from '@tanstack/react-query';
import { useLocale, useTranslations } from 'next-intl';
import { Link, useRouter } from '@/i18n/navigation';
import { Button } from '@/components/ui/button';
import { logout, getMe } from '@/lib/api/endpoints';
import { getStoredToken, hasAdminRole, hasEditorialRole, setStoredToken } from '@/lib/auth/token';

export function AuthNav() {
  const t = useTranslations('nav');
  const tAuth = useTranslations('auth');
  const locale = useLocale();
  const router = useRouter();
  const queryClient = useQueryClient();
  const hasToken = typeof window !== 'undefined' && Boolean(getStoredToken());

  const meQuery = useQuery({
    queryKey: ['me', locale],
    queryFn: () => getMe(locale),
    enabled: hasToken,
    retry: false,
  });

  const user = meQuery.data?.data;
  const isEditor = user ? hasEditorialRole(user.roles) : false;
  const isAdmin = user ? hasAdminRole(user.roles) : false;

  const handleLogout = async () => {
    try {
      await logout(locale);
    } catch {
      // Clear local session even if API call fails.
    }
    setStoredToken(null);
    queryClient.removeQueries({ queryKey: ['me'] });
    router.push('/');
    router.refresh();
  };

  if (!hasToken || meQuery.isLoading) {
    return (
      <>
        <Link href="/login">
          <Button variant="ghost" size="sm">
            {t('login')}
          </Button>
        </Link>
        <Link href="/register">
          <Button size="sm">{t('register')}</Button>
        </Link>
      </>
    );
  }

  if (!user) {
    return (
      <Link href="/login">
        <Button variant="ghost" size="sm">
          {t('login')}
        </Button>
      </Link>
    );
  }

  return (
    <>
      <Link href="/account">
        <Button variant="ghost" size="sm">
          {t('account')}
        </Button>
      </Link>
      {isEditor ? (
        <Link href="/cms">
          <Button variant="ghost" size="sm">
            {t('cms')}
          </Button>
        </Link>
      ) : null}
      {isAdmin ? (
        <Link href="/admin">
          <Button variant="ghost" size="sm">
            {t('admin')}
          </Button>
        </Link>
      ) : null}
      <Button variant="secondary" size="sm" onClick={handleLogout}>
        {tAuth('logout')}
      </Button>
    </>
  );
}
