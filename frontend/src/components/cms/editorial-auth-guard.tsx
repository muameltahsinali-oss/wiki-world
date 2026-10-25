'use client';

import { useEffect, useState } from 'react';
import { useLocale, useTranslations } from 'next-intl';
import { useRouter } from '@/i18n/navigation';
import { ApiClientError } from '@/lib/api/client';
import { getMe } from '@/lib/api/endpoints';
import { hasEditorialRole } from '@/lib/auth/token';
import { Skeleton } from '@/components/ui/skeleton';

export function EditorialAuthGuard({ children }: { children: React.ReactNode }) {
  const t = useTranslations('cms');
  const locale = useLocale();
  const router = useRouter();
  const [status, setStatus] = useState<'loading' | 'authorized' | 'denied'>('loading');

  useEffect(() => {
    let active = true;

    getMe(locale)
      .then((response) => {
        if (!active) return;
        if (hasEditorialRole(response.data.roles)) {
          setStatus('authorized');
        } else {
          setStatus('denied');
        }
      })
      .catch((error) => {
        if (!active) return;
        if (error instanceof ApiClientError && (error.status === 401 || error.status === 403)) {
          setStatus('denied');
          router.replace('/login');
          return;
        }
        setStatus('denied');
      });

    return () => {
      active = false;
    };
  }, [locale, router]);

  if (status === 'loading') {
    return (
      <div className="flex min-h-[50vh] flex-col items-center justify-center gap-4 p-8">
        <Skeleton className="h-8 w-48" />
        <p className="text-sm text-[var(--color-ink-secondary)]">{t('redirecting')}</p>
      </div>
    );
  }

  if (status === 'denied') {
    return (
      <div className="flex min-h-[50vh] flex-col items-center justify-center gap-3 p-8 text-center">
        <p className="text-sm text-[var(--color-ink-secondary)]">{t('accessDenied')}</p>
        <p className="text-xs text-[var(--color-ink-secondary)]">{t('accessDeniedHint')}</p>
      </div>
    );
  }

  return <>{children}</>;
}
