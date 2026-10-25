'use client';

import { useLocale, useTranslations } from 'next-intl';
import { useQuery } from '@tanstack/react-query';
import { VerseBlock } from '@/components/quran/verse-block';
import { Skeleton } from '@/components/ui/skeleton';
import { getQuranSurah, getQuranSurahVerses } from '@/lib/api/quran';

export function QuranSurahReaderPage({ number }: { number: number }) {
  const locale = useLocale();
  const t = useTranslations('quran');

  const surahQuery = useQuery({
    queryKey: ['quran-surah', number, locale],
    queryFn: () => getQuranSurah(locale, number),
  });

  const versesQuery = useQuery({
    queryKey: ['quran-surah-verses', number, locale],
    queryFn: () => getQuranSurahVerses(locale, number),
  });

  const surah = surahQuery.data?.data;
  const verses = versesQuery.data?.data ?? surah?.verses_preview ?? [];

  return (
    <main className="mx-auto flex w-full max-w-3xl flex-col gap-8 px-4 py-10 sm:px-6">
      {surahQuery.isLoading ? (
        <Skeleton className="h-24 w-full" />
      ) : surah ? (
        <header className="flex flex-col gap-2 border-b border-[var(--color-border-default)] pb-6">
          <p className="text-sm text-[var(--color-wisdom)]">
            {t('surah')} {surah.number}
          </p>
          <h1 className="text-3xl font-bold text-[var(--color-ink)]" lang="ar" dir="rtl">
            {surah.name_ar}
          </h1>
          <p className="text-[var(--color-ink-secondary)]">
            {surah.name_translated} · {surah.verse_count} {t('verses')} · {t(surah.revelation_type)}
          </p>
          {surah.bismillah ? (
            <p className="mt-4 text-xl leading-loose text-[var(--color-ink)]" lang="ar" dir="rtl">
              {surah.bismillah}
            </p>
          ) : null}
        </header>
      ) : null}

      {versesQuery.isLoading ? (
        <div className="flex flex-col gap-4">
          {Array.from({ length: 5 }).map((_, i) => (
            <Skeleton key={i} className="h-24 rounded-lg" />
          ))}
        </div>
      ) : (
        <div className="flex flex-col gap-4">
          {verses.map((verse) => (
            <VerseBlock key={`${verse.surah}:${verse.ayah}`} verse={verse} />
          ))}
        </div>
      )}
    </main>
  );
}
