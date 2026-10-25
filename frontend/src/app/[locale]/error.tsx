'use client';

import { useTranslations } from 'next-intl';
import { Button } from '@/components/ui/button';

export default function ErrorPage({
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  const t = useTranslations('common');
  const errors = useTranslations('errors');

  return (
    <main className="mx-auto flex min-h-[60vh] max-w-lg flex-col items-center justify-center gap-4 px-6 text-center">
      <h1 className="text-2xl font-semibold text-[var(--color-ink)]">{t('error')}</h1>
      <p className="text-sm text-[var(--color-ink-secondary)]">{errors('serverError')}</p>
      <Button type="button" onClick={reset}>
        {t('retry')}
      </Button>
    </main>
  );
}
