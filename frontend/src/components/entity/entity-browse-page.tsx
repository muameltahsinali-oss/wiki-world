'use client';

import { useState } from 'react';
import { useLocale, useTranslations } from 'next-intl';
import { useQuery } from '@tanstack/react-query';
import { EntityCard } from '@/components/ui/entity-card';
import { EmptyState } from '@/components/ui/empty-state';
import { Input } from '@/components/ui/input';
import { Pagination } from '@/components/ui/pagination';
import { Skeleton } from '@/components/ui/skeleton';
import type { EntityType } from '@/components/ui/badge';
import { getPublicEntities } from '@/lib/api/public';
import { ENTITY_TYPE_ROUTES, entityPublicHref } from '@/lib/entity/types';
import { useEntityTypeLabel } from '@/lib/entity/person-label';

export interface EntityBrowsePageProps {
  typeRoute: string;
  entityType: EntityType;
  moduleFilter?: string;
  titleKey?: string;
}

export function EntityBrowsePage({
  typeRoute,
  entityType,
  moduleFilter,
  titleKey,
}: EntityBrowsePageProps) {
  const locale = useLocale();
  const t = useTranslations('public.browse');
  const entityTypeLabel = useEntityTypeLabel();
  const [page, setPage] = useState(1);
  const [query, setQuery] = useState('');

  const listQuery = useQuery({
    queryKey: ['public-entities', typeRoute, locale, page, query, moduleFilter],
    queryFn: () =>
      getPublicEntities(locale, typeRoute, {
        page,
        per_page: 12,
        q: query || undefined,
        module: moduleFilter,
      }),
  });

  const items = listQuery.data?.data ?? [];
  const pagination = listQuery.data?.meta?.pagination;
  const totalPages = pagination?.last_page ?? 1;

  return (
    <main className="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-10 sm:px-6">
      <header className="flex flex-col gap-4">
        <h1 className="text-[length:var(--text-h1)] font-bold text-[var(--color-ink)]">
          {titleKey ? t(titleKey) : entityTypeLabel(entityType)}
        </h1>
        <p className="max-w-2xl text-[var(--color-ink-secondary)]">{t('subtitle')}</p>
        <div className="max-w-md">
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
      </header>

      {listQuery.isLoading ? (
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
              entityType={item.type ?? entityType}
              typeLabel={entityTypeLabel(item.type ?? entityType, {
                hints: item.person_hints,
                title: item.title,
                module: item.module ?? moduleFilter,
              })}
              href={entityPublicHref(item.type ?? entityType, item.uuid)}
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

export function AhlulbaytPersonsBrowsePage() {
  return (
    <EntityBrowsePage
      typeRoute={ENTITY_TYPE_ROUTES.person}
      entityType="person"
      moduleFilter="ahlulbayt"
      titleKey="ahlulbaytPersons"
    />
  );
}
