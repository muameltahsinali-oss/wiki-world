'use client';

import { useLocale, useTranslations } from 'next-intl';
import { useQuery } from '@tanstack/react-query';
import { Link } from '@/i18n/navigation';
import { getQuranLanding } from '@/lib/api/quran';
import { Skeleton } from '@/components/ui/skeleton';

export function QuranLandingPage() {
  const locale = useLocale();
  const t = useTranslations('quran');

  const landingQuery = useQuery({
    queryKey: ['quran-landing', locale],
    queryFn: () => getQuranLanding(locale),
  });

  const data = landingQuery.data?.data;

  return (
    <main className="mx-auto flex w-full max-w-7xl flex-col gap-10 px-4 py-10 sm:px-6">
      <header className="flex flex-col gap-4">
        <h1 className="text-[length:var(--text-h1)] font-bold text-[var(--color-ink)]">{t('title')}</h1>
        <p className="max-w-2xl text-lg text-[var(--color-ink-secondary)]">{t('subtitle')}</p>
        <div className="flex flex-wrap gap-3">
          <Link
            href="/quran/surahs"
            className="inline-flex h-10 items-center justify-center rounded-md bg-[var(--color-scholar)] px-4 text-sm font-medium text-white hover:bg-[var(--color-scholar-hover)]"
          >
            {t('browseSurahs')}
          </Link>
        </div>
      </header>

      {landingQuery.isLoading ? (
        <Skeleton className="h-40 w-full rounded-lg" />
      ) : (
        <>
          {data?.featured_surahs?.length ? (
            <section className="flex flex-col gap-4">
              <h2 className="text-xl font-semibold text-[var(--color-ink)]">{t('featuredSurahs')}</h2>
              <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {data.featured_surahs.map((surah) => (
                  <Link
                    key={surah.number}
                    href={`/quran/surahs/${surah.number}`}
                    className="rounded-lg border border-[var(--color-border-default)] bg-[var(--color-wisdom-subtle)] p-4 transition-colors hover:border-[var(--color-wisdom)]"
                  >
                    <p className="text-sm text-[var(--color-wisdom)]">{surah.number}</p>
                    <p className="text-lg font-semibold text-[var(--color-ink)]" lang="ar" dir="rtl">
                      {surah.name_ar}
                    </p>
                    <p className="text-sm text-[var(--color-ink-secondary)]">{surah.name_translated}</p>
                  </Link>
                ))}
              </div>
            </section>
          ) : null}

          {data?.featured_topics?.length ? (
            <section className="flex flex-col gap-4">
              <h2 className="text-xl font-semibold text-[var(--color-ink)]">{t('featuredTopics')}</h2>
              <div className="flex flex-wrap gap-2">
                {data.featured_topics.map((topic) => (
                  <span
                    key={topic.slug}
                    className="rounded-full bg-[var(--color-surface-sunken)] px-3 py-1 text-sm text-[var(--color-ink-secondary)]"
                  >
                    {topic.title}
                  </span>
                ))}
              </div>
            </section>
          ) : null}
        </>
      )}
    </main>
  );
}
