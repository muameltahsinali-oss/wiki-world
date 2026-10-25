'use client';

import { Filter, X } from 'lucide-react';
import { useLocale, useTranslations } from 'next-intl';
import { useMemo, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { useSearchParams } from 'next/navigation';
import { Badge, entityBadgeVariant, type EntityType } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Drawer } from '@/components/ui/drawer';
import { EmptyState } from '@/components/ui/empty-state';
import { Input } from '@/components/ui/input';
import { Pagination } from '@/components/ui/pagination';
import { Skeleton } from '@/components/ui/skeleton';
import { Link, useRouter } from '@/i18n/navigation';
import { search as searchApi } from '@/lib/api/search';
import { entityPublicHref } from '@/lib/entity/types';
import { useEntityTypeLabel } from '@/lib/entity/person-label';
import { renderHighlight } from '@/lib/search/highlight';
import { cn } from '@/lib/utils';

const ENTITY_TYPES: EntityType[] = [
  'person',
  'event',
  'place',
  'concept',
  'source',
  'book',
  'article',
  'verse',
];

const MODULES = ['quran', 'ahlulbayt', 'general', 'books_sources'] as const;

const SUGGESTED_QUERIES = ['Ghadir', 'Ashura', 'Nahj al-Balagha', 'Imam Ali'];

function moduleLabel(module: string, t: ReturnType<typeof useTranslations<'search'>>) {
  if (module === 'quran' || module === 'ahlulbayt' || module === 'general' || module === 'books_sources') {
    return t(`modules.${module}`);
  }
  return module;
}

export function SearchPage({
  initialQuery = '',
  initialType = '',
  initialModule = '',
}: {
  initialQuery?: string;
  initialType?: string;
  initialModule?: string;
}) {
  const t = useTranslations('search');
  const personLabels = useTranslations('personLabels');
  const entityTypeLabel = useEntityTypeLabel();
  const locale = useLocale();
  const router = useRouter();
  const searchParams = useSearchParams();

  const q = searchParams.get('q') ?? initialQuery;
  const typeFilter = searchParams.get('type') ?? initialType;
  const moduleFilter = searchParams.get('module') ?? initialModule;
  const page = Math.max(1, Number(searchParams.get('page') ?? '1') || 1);

  const [draftQuery, setDraftQuery] = useState(q);
  const [filtersOpen, setFiltersOpen] = useState(false);

  const searchQuery = useQuery({
    queryKey: ['search', locale, q, typeFilter, moduleFilter, page],
    queryFn: () =>
      searchApi(
        q,
        {
          type: typeFilter || undefined,
          module: moduleFilter || undefined,
          page,
          per_page: 20,
        },
        locale,
      ),
    enabled: q.trim().length >= 2,
  });

  const results = searchQuery.data?.data ?? [];
  const meta = searchQuery.data?.meta;
  const totalPages = meta?.pagination.last_page ?? 1;
  const facets = meta?.facets;
  const zeroSuggestions = meta?.suggestions?.length
    ? meta.suggestions
    : SUGGESTED_QUERIES;

  const updateParams = (next: {
    q?: string;
    type?: string;
    module?: string;
    page?: number;
  }) => {
    const params = new URLSearchParams();
    const nextQ = next.q ?? q;
    const nextType = next.type === undefined ? typeFilter : next.type;
    const nextModule = next.module === undefined ? moduleFilter : next.module;
    const nextPage = next.page ?? 1;

    if (nextQ) params.set('q', nextQ);
    if (nextType) params.set('type', nextType);
    if (nextModule) params.set('module', nextModule);
    if (nextPage > 1) params.set('page', String(nextPage));

    router.push(`/search?${params.toString()}`);
  };

  const filterTypeLabel = (type: EntityType) =>
    type === 'person' ? personLabels('browseFilter') : entityTypeLabel(type);

  const filtersSummary = useMemo(() => {
    const parts: string[] = [];
    if (typeFilter) parts.push(filterTypeLabel(typeFilter as EntityType));
    if (moduleFilter) parts.push(moduleLabel(moduleFilter, t));
    return parts;
  }, [entityTypeLabel, moduleFilter, personLabels, t, typeFilter]);

  const FiltersPanel = (
    <div className="flex flex-col gap-6">
      <fieldset className="flex flex-col gap-2">
        <legend className="text-sm font-semibold text-[var(--color-ink)]">{t('filters.type')}</legend>
        <label className="flex items-center gap-2 text-sm text-[var(--color-ink-secondary)]">
          <input
            type="radio"
            name="type"
            checked={!typeFilter}
            onChange={() => updateParams({ type: '', page: 1 })}
          />
          {t('filters.allTypes')}
        </label>
        {ENTITY_TYPES.map((type) => (
          <label
            key={type}
            className="flex items-center justify-between gap-2 text-sm text-[var(--color-ink-secondary)]"
          >
            <span className="flex items-center gap-2">
              <input
                type="radio"
                name="type"
                checked={typeFilter === type}
                onChange={() => updateParams({ type, page: 1 })}
              />
              {filterTypeLabel(type)}
            </span>
            {facets?.type?.find((bucket) => bucket.value === type)?.count != null ? (
              <span className="text-xs text-[var(--color-ink-tertiary)]">
                {facets.type.find((bucket) => bucket.value === type)?.count}
              </span>
            ) : null}
          </label>
        ))}
      </fieldset>

      <fieldset className="flex flex-col gap-2">
        <legend className="text-sm font-semibold text-[var(--color-ink)]">{t('filters.module')}</legend>
        <label className="flex items-center gap-2 text-sm text-[var(--color-ink-secondary)]">
          <input
            type="radio"
            name="module"
            checked={!moduleFilter}
            onChange={() => updateParams({ module: '', page: 1 })}
          />
          {t('filters.allModules')}
        </label>
        {MODULES.map((module) => (
          <label
            key={module}
            className="flex items-center justify-between gap-2 text-sm text-[var(--color-ink-secondary)]"
          >
            <span className="flex items-center gap-2">
              <input
                type="radio"
                name="module"
                checked={moduleFilter === module}
                onChange={() => updateParams({ module, page: 1 })}
              />
              {t(`modules.${module}`)}
            </span>
            {facets?.module?.find((bucket) => bucket.value === module)?.count != null ? (
              <span className="text-xs text-[var(--color-ink-tertiary)]">
                {facets.module.find((bucket) => bucket.value === module)?.count}
              </span>
            ) : null}
          </label>
        ))}
      </fieldset>

      {(typeFilter || moduleFilter) && (
        <Button
          type="button"
          variant="ghost"
          size="sm"
          onClick={() => updateParams({ type: '', module: '', page: 1 })}
        >
          <X className="h-4 w-4" aria-hidden />
          {t('filters.clear')}
        </Button>
      )}
    </div>
  );

  return (
    <main className="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-10 sm:px-6">
      <header className="flex flex-col gap-4">
        <h1 className="text-[length:var(--text-h1)] font-bold text-[var(--color-ink)]">{t('title')}</h1>
        <p className="max-w-2xl text-[var(--color-ink-secondary)]">{t('subtitle')}</p>

        <form
          className="flex flex-col gap-3 sm:flex-row"
          onSubmit={(event) => {
            event.preventDefault();
            updateParams({ q: draftQuery.trim(), page: 1 });
          }}
        >
          <div className="flex-1">
            <Input
              label={t('queryLabel')}
              value={draftQuery}
              onChange={(event) => setDraftQuery(event.target.value)}
              placeholder={t('queryPlaceholder')}
            />
          </div>
          <div className="flex items-end gap-2">
            <Button type="submit">{t('submit')}</Button>
            <Button
              type="button"
              variant="secondary"
              className="lg:hidden"
              onClick={() => setFiltersOpen(true)}
              aria-label={t('filters.open')}
            >
              <Filter className="h-4 w-4" aria-hidden />
              {t('filters.open')}
            </Button>
          </div>
        </form>

        {filtersSummary.length > 0 ? (
          <div className="flex flex-wrap gap-2">
            {filtersSummary.map((label) => (
              <Badge key={label} variant="default">
                {label}
              </Badge>
            ))}
          </div>
        ) : null}
      </header>

      <div className="grid gap-8 lg:grid-cols-[16rem_1fr]">
        <aside className="hidden lg:block">
          <div className="sticky top-[calc(var(--header-height)+1rem)] rounded-lg border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] p-4">
            <h2 className="mb-4 text-sm font-semibold text-[var(--color-ink)]">{t('filters.title')}</h2>
            {FiltersPanel}
          </div>
        </aside>

        <section aria-live="polite">
          {!q.trim() ? (
            <EmptyState title={t('emptyQueryTitle')} description={t('emptyQueryDescription')} />
          ) : q.trim().length < 2 ? (
            <EmptyState title={t('minQueryTitle')} description={t('minQueryDescription')} />
          ) : searchQuery.isLoading ? (
            <div className="flex flex-col gap-4">
              {Array.from({ length: 5 }).map((_, index) => (
                <Skeleton key={index} className="h-28 w-full rounded-lg" />
              ))}
            </div>
          ) : searchQuery.isError ? (
            <EmptyState
              title={t('errorTitle')}
              description={t('errorDescription')}
              actionLabel={t('retry')}
              onAction={() => searchQuery.refetch()}
            />
          ) : results.length === 0 ? (
            <div className="flex flex-col gap-6">
              <EmptyState title={t('zeroTitle')} description={t('zeroDescription')} />
              <div>
                <p className="mb-3 text-sm font-medium text-[var(--color-ink-secondary)]">
                  {t('trySuggestions')}
                </p>
                <div className="flex flex-wrap gap-2">
                  {zeroSuggestions.map((suggestion) => (
                    <button
                      key={suggestion}
                      type="button"
                      className="rounded-full border border-[var(--color-border-default)] px-3 py-1.5 text-sm text-[var(--color-ink-secondary)] hover:border-[var(--color-scholar)] hover:text-[var(--color-scholar)]"
                      onClick={() => {
                        setDraftQuery(suggestion);
                        updateParams({ q: suggestion, page: 1 });
                      }}
                    >
                      {suggestion}
                    </button>
                  ))}
                </div>
              </div>
            </div>
          ) : (
            <div className="flex flex-col gap-4">
              <p className="text-sm text-[var(--color-ink-secondary)]">
                {t('resultsCount', { count: meta?.total ?? results.length, query: q })}
              </p>
              <ul className="flex flex-col gap-3">
                {results.map((item) => (
                  <li key={item.uuid}>
                    <Link
                      href={entityPublicHref(item.type, item.uuid, item.slug)}
                      className={cn(
                        'block rounded-lg border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] p-4 transition-colors hover:border-[var(--color-scholar)]',
                      )}
                    >
                      <div className="mb-2 flex flex-wrap items-center gap-2">
                        <Badge variant={entityBadgeVariant(item.type)}>
                          {entityTypeLabel(item.type, {
                            title: item.title,
                            module: item.module,
                          })}
                        </Badge>
                        {item.module ? (
                          <span className="text-xs text-[var(--color-ink-tertiary)]">
                            {moduleLabel(item.module, t)}
                          </span>
                        ) : null}
                      </div>
                      <h2 className="text-lg font-semibold text-[var(--color-ink)]">
                        {item.highlight?.title
                          ? renderHighlight(item.highlight.title)
                          : item.title}
                      </h2>
                      {(item.highlight?.summary || item.summary) && (
                        <p className="mt-2 text-sm leading-relaxed text-[var(--color-ink-secondary)]">
                          {item.highlight?.summary
                            ? renderHighlight(item.highlight.summary)
                            : item.summary}
                        </p>
                      )}
                    </Link>
                  </li>
                ))}
              </ul>

              {totalPages > 1 ? (
                <Pagination
                  page={page}
                  totalPages={totalPages}
                  onPageChange={(nextPage) => updateParams({ page: nextPage })}
                />
              ) : null}
            </div>
          )}
        </section>
      </div>

      <Drawer open={filtersOpen} onOpenChange={setFiltersOpen} title={t('filters.title')} side="start">
        {FiltersPanel}
      </Drawer>
    </main>
  );
}
