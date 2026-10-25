'use client';

import { useState } from 'react';
import { useLocale, useTranslations } from 'next-intl';
import { useQuery } from '@tanstack/react-query';
import { EntityCard } from '@/components/ui/entity-card';
import { EmptyState } from '@/components/ui/empty-state';
import { Input } from '@/components/ui/input';
import { Pagination } from '@/components/ui/pagination';
import { Skeleton } from '@/components/ui/skeleton';
import { getAhlulbaytBrowse } from '@/lib/api/ahlulbayt';
import { entityPublicHref } from '@/lib/entity/types';
import { useEntityTypeLabel } from '@/lib/entity/person-label';

export function AhlulbaytBrowsePage() {
  const locale = useLocale();
  const t = useTranslations('ahlulbayt.browse');
  const personLabels = useTranslations('personLabels');
  const entityTypeLabel = useEntityTypeLabel();
  const [page, setPage] = useState(1);
  const [query, setQuery] = useState('');
  const [typeFilter, setTypeFilter] = useState('');
  const [masumOnly, setMasumOnly] = useState(false);

  const browseQuery = useQuery({
    queryKey: ['ahlulbayt-browse', locale, page, query, typeFilter, masumOnly],
    queryFn: () =>
      getAhlulbaytBrowse(locale, {
        page,
        per_page: 12,
        q: query || undefined,
        type: typeFilter || undefined,
        is_masum: masumOnly || undefined,
      }),
  });

  const items = browseQuery.data?.data ?? [];
  const pagination = browseQuery.data?.meta?.pagination;
  const totalPages = pagination?.last_page ?? 1;

  return (
    <main className="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-10 sm:px-6">
      <header className="flex flex-col gap-4">
        <h1 className="text-[length:var(--text-h1)] font-bold text-[var(--color-ink)]">{t('title')}</h1>
        <p className="max-w-2xl text-[var(--color-ink-secondary)]">{t('subtitle')}</p>
        <div className="flex flex-wrap gap-4">
          <div className="min-w-[200px] flex-1">
            <Input
              label={t('searchLabel')}
              value={query}
              onChange={(event) => {
                setQuery(event.target.value);
                setPage(1);
              }}
              placeholder={t('searchPlaceholder')}
            />
          </div>
          <div className="flex flex-col gap-1.5">
            <label htmlFor="type-filter" className="text-sm font-medium text-[var(--color-ink)]">
              {t('typeFilter')}
            </label>
            <select
              id="type-filter"
              value={typeFilter}
              onChange={(event) => {
                setTypeFilter(event.target.value);
                setPage(1);
              }}
              className="flex h-10 rounded-md border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] px-3 text-sm"
            >
              <option value="">{t('allTypes')}</option>
              <option value="person">{personLabels('browseFilter')}</option>
              <option value="event">{entityTypeLabel('event')}</option>
              <option value="place">{entityTypeLabel('place')}</option>
            </select>
          </div>
          <label className="flex items-end gap-2 pb-2 text-sm">
            <input
              type="checkbox"
              checked={masumOnly}
              onChange={(event) => {
                setMasumOnly(event.target.checked);
                setPage(1);
              }}
            />
            {t('masumOnly')}
          </label>
        </div>
      </header>

      {browseQuery.isLoading ? (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {Array.from({ length: 6 }).map((_, index) => (
            <Skeleton key={index} className="h-32 w-full rounded-lg" />
          ))}
        </div>
      ) : items.length === 0 ? (
        <EmptyState title={t('emptyTitle')} description={t('emptyDescription')} />
      ) : (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {items.map((item) => (
            <EntityCard
              key={item.uuid}
              uuid={item.uuid}
              title={item.title}
              summary={item.summary ?? undefined}
              entityType={item.type}
              typeLabel={entityTypeLabel(item.type, {
                hints: item.person_hints,
                title: item.title,
                module: 'ahlulbayt',
              })}
              href={entityPublicHref(item.type, item.uuid)}
            />
          ))}
        </div>
      )}

      {pagination && totalPages > 1 ? (
        <Pagination page={page} totalPages={totalPages} onPageChange={setPage} />
      ) : null}
    </main>
  );
}
