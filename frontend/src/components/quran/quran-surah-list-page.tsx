'use client';

import { useLocale, useTranslations } from 'next-intl';
import { useQuery } from '@tanstack/react-query';
import { Link } from '@/i18n/navigation';
import { getQuranSurahs } from '@/lib/api/quran';
import { Skeleton } from '@/components/ui/skeleton';
import { EmptyState } from '@/components/ui/empty-state';

export function QuranSurahListPage() {
  const locale = useLocale();
  const t = useTranslations('quran');

  const surahsQuery = useQuery({
    queryKey: ['quran-surahs', locale],
    queryFn: () => getQuranSurahs(locale),
  });

  const surahs = surahsQuery.data?.data ?? [];

  return (
    <main className="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-10 sm:px-6">
      <header>
        <h1 className="text-[length:var(--text-h1)] font-bold text-[var(--color-ink)]">{t('surahList')}</h1>
        <p className="mt-2 text-[var(--color-ink-secondary)]">{t('surahListSubtitle')}</p>
      </header>

      {surahsQuery.isLoading ? (
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          {Array.from({ length: 12 }).map((_, i) => (
            <Skeleton key={i} className="h-20 rounded-lg" />
          ))}
        </div>
      ) : surahs.length === 0 ? (
        <EmptyState title={t('noSurahs')} description={t('noSurahsHint')} />
      ) : (
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          {surahs.map((surah) => (
            <Link
              key={surah.number}
              href={`/quran/surahs/${surah.number}`}
              className="flex items-center gap-4 rounded-lg border border-[var(--color-border-default)] p-4 transition-colors hover:border-[var(--color-wisdom)] hover:bg-[var(--color-wisdom-subtle)]"
            >
              <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[var(--color-wisdom-subtle)] text-sm font-bold text-[var(--color-wisdom)]">
                {surah.number}
              </span>
              <div className="min-w-0 flex-1">
                <p className="truncate font-semibold text-[var(--color-ink)]" lang="ar" dir="rtl">
                  {surah.name_ar}
                </p>
                <p className="truncate text-sm text-[var(--color-ink-secondary)]">
                  {surah.name_translated} · {surah.verse_count} {t('verses')}
                </p>
              </div>
            </Link>
          ))}
        </div>
      )}
    </main>
  );
}
