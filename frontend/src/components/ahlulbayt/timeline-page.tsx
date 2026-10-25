'use client';

import { useTranslations } from 'next-intl';
import { Timeline } from '@/components/ahlulbayt/timeline';

export function TimelinePage() {
  const t = useTranslations('ahlulbayt.timeline');

  return (
    <main className="mx-auto flex w-full max-w-3xl flex-col gap-8 px-4 py-10 sm:px-6">
      <header>
        <h1 className="text-[length:var(--text-h1)] font-bold text-[var(--color-ink)]">{t('pageTitle')}</h1>
        <p className="mt-2 text-[var(--color-ink-secondary)]">{t('pageSubtitle')}</p>
      </header>
      <Timeline />
    </main>
  );
}
