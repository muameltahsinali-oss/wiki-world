'use client';

import { useLocale, useTranslations } from 'next-intl';
import { useQuery } from '@tanstack/react-query';
import { VerseBlock } from '@/components/quran/verse-block';
import { Tabs } from '@/components/ui/tabs';
import { Skeleton } from '@/components/ui/skeleton';
import { Link } from '@/i18n/navigation';
import { getQuranVerse, parseVerseReference } from '@/lib/api/quran';
import { entityPublicHref } from '@/lib/entity/types';

export function QuranVerseDetailPage({ reference }: { reference: string }) {
  const locale = useLocale();
  const t = useTranslations('quran');
  const parsed = parseVerseReference(reference);

  const verseQuery = useQuery({
    queryKey: ['quran-verse', reference, locale],
    queryFn: () => getQuranVerse(locale, parsed!.surah, parsed!.ayah),
    enabled: parsed !== null,
  });

  const verse = verseQuery.data?.data;

  if (!parsed) {
    return (
      <main className="mx-auto max-w-3xl px-4 py-12">
        <p className="text-[var(--color-ink-secondary)]">{t('invalidReference')}</p>
      </main>
    );
  }

  return (
    <main className="mx-auto flex w-full max-w-3xl flex-col gap-8 px-4 py-10 sm:px-6">
      {verseQuery.isLoading ? (
        <Skeleton className="h-40 w-full rounded-lg" />
      ) : verse ? (
        <>
          <VerseBlock verse={verse} showReference={false} />
          <Tabs
            defaultTab="translations"
            items={[
              {
                id: 'translations',
                label: t('translations'),
                content: (
                  <div className="flex flex-col gap-3">
                    {verse.translations?.length ? (
                      verse.translations.map((translation, index) => (
                        <blockquote
                          key={index}
                          className="rounded-lg border border-[var(--color-border-default)] p-4 text-sm"
                        >
                          <p className="text-[var(--color-ink)]">{translation.text}</p>
                          <footer className="mt-2 text-xs text-[var(--color-ink-tertiary)]">
                            {translation.translator}
                          </footer>
                        </blockquote>
                      ))
                    ) : (
                      <p className="text-sm text-[var(--color-ink-secondary)]">{t('noTranslations')}</p>
                    )}
                  </div>
                ),
              },
              {
                id: 'tafsir',
                label: t('tafsir'),
                content: (
                  <div className="flex flex-col gap-3">
                    {verse.tafsir?.length ? (
                      verse.tafsir.map((item, index) => (
                        <div key={index} className="rounded-lg bg-[var(--color-surface-sunken)] p-4 text-sm">
                          <p className="font-medium text-[var(--color-ink)]">{item.source}</p>
                          <p className="mt-2 text-[var(--color-ink-secondary)]">{item.excerpt}</p>
                        </div>
                      ))
                    ) : (
                      <p className="text-sm text-[var(--color-ink-secondary)]">{t('noTafsir')}</p>
                    )}
                  </div>
                ),
              },
              {
                id: 'related',
                label: t('related'),
                content: (
                  <div className="flex flex-col gap-2">
                    {verse.related_entities?.length ? (
                      verse.related_entities.map((entity) => (
                        <Link
                          key={entity.uuid}
                          href={entityPublicHref(entity.type, entity.uuid)}
                          className="text-sm text-[var(--color-scholar)] hover:underline"
                        >
                          {entity.title}
                        </Link>
                      ))
                    ) : (
                      <p className="text-sm text-[var(--color-ink-secondary)]">{t('noRelated')}</p>
                    )}
                  </div>
                ),
              },
            ]}
          />
        </>
      ) : (
        <p className="text-[var(--color-ink-secondary)]">{t('verseNotFound')}</p>
      )}
    </main>
  );
}
