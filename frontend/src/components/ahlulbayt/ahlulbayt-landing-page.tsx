'use client';

import { useLocale, useTranslations } from 'next-intl';
import { useQuery } from '@tanstack/react-query';
import { GenealogyTree } from '@/components/ahlulbayt/genealogy-tree';
import { Timeline } from '@/components/ahlulbayt/timeline';
import { EntityCard } from '@/components/ui/entity-card';
import { Link } from '@/i18n/navigation';
import { getAhlulbaytLanding } from '@/lib/api/ahlulbayt';
import { entityPublicHref } from '@/lib/entity/types';
import { useEntityTypeLabel } from '@/lib/entity/person-label';
import { Skeleton } from '@/components/ui/skeleton';

export function AhlulbaytLandingPage() {
  const locale = useLocale();
  const t = useTranslations('ahlulbayt');
  const entityTypeLabel = useEntityTypeLabel();

  const landingQuery = useQuery({
    queryKey: ['ahlulbayt-landing', locale],
    queryFn: () => getAhlulbaytLanding(locale),
  });

  const data = landingQuery.data?.data;

  return (
    <main className="mx-auto flex w-full max-w-7xl flex-col gap-10 px-4 py-10 sm:px-6">
      <header className="flex flex-col gap-4">
        <h1 className="text-[length:var(--text-h1)] font-bold text-[var(--color-ink)]">
          {data?.title ?? t('title')}
        </h1>
        <p className="max-w-2xl text-lg text-[var(--color-ink-secondary)]">{t('subtitle')}</p>
        <div className="flex flex-wrap gap-3">
          <Link
            href="/ahlulbayt/browse"
            className="inline-flex h-10 items-center justify-center rounded-md bg-[var(--color-scholar)] px-4 text-sm font-medium text-white hover:bg-[var(--color-scholar-hover)]"
          >
            {t('browseLink')}
          </Link>
          <Link
            href="/timeline"
            className="inline-flex h-10 items-center justify-center rounded-md border border-[var(--color-scholar)] px-4 text-sm font-medium text-[var(--color-scholar)] hover:bg-[var(--color-scholar-subtle)]"
          >
            {t('timelineLink')}
          </Link>
        </div>
      </header>

      {landingQuery.isLoading ? (
        <Skeleton className="h-48 w-full rounded-lg" />
      ) : (
        <>
          {data?.featured_persons?.length ? (
            <section className="flex flex-col gap-4">
              <h2 className="text-xl font-semibold text-[var(--color-ink)]">{t('featuredPersons')}</h2>
              <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {data.featured_persons.map((person) => (
                  <EntityCard
                    key={person.uuid}
                    uuid={person.uuid}
                    title={person.title}
                    summary={person.summary ?? undefined}
                    entityType="person"
                    typeLabel={entityTypeLabel('person', {
                      hints: person.person_hints,
                      title: person.title,
                      module: 'ahlulbayt',
                    })}
                    href={entityPublicHref('person', person.uuid)}
                  />
                ))}
              </div>
            </section>
          ) : null}

          {data?.featured_events?.length ? (
            <section className="flex flex-col gap-4">
              <h2 className="text-xl font-semibold text-[var(--color-ink)]">{t('featuredEvents')}</h2>
              <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {data.featured_events.map((event) => (
                  <EntityCard
                    key={event.uuid}
                    uuid={event.uuid}
                    title={event.title}
                    summary={event.summary ?? undefined}
                    entityType="event"
                    typeLabel={entityTypeLabel('event')}
                    href={entityPublicHref('event', event.uuid)}
                  />
                ))}
              </div>
            </section>
          ) : null}

          <GenealogyTree />
          <section>
            <h2 className="mb-4 text-xl font-semibold text-[var(--color-ink)]">{t('timelinePreview')}</h2>
            <Timeline compact />
          </section>
        </>
      )}
    </main>
  );
}
