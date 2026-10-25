'use client';

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useLocale, useTranslations } from 'next-intl';
import { useMemo, useState } from 'react';
import { Link, useRouter } from '@/i18n/navigation';
import { WorkflowBadge } from '@/components/cms/workflow-badge';
import { Badge, entityBadgeVariant } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import { getEditorialEntities, createEntityDraft } from '@/lib/api/editorial';
import type { EntityType } from '@/components/ui/badge';
import type { WorkflowState } from '@/lib/cms/types';

const ENTITY_TYPES: EntityType[] = [
  'person',
  'article',
  'event',
  'place',
  'concept',
  'source',
  'book',
];

const WORKFLOW_STATES: WorkflowState[] = [
  'draft',
  'submitted',
  'in_review',
  'approved',
  'published',
];

export function EntityListPage() {
  const t = useTranslations('cms');
  const tTypes = useTranslations('entityTypes');
  const locale = useLocale();
  const router = useRouter();
  const queryClient = useQueryClient();
  const [query, setQuery] = useState('');
  const [type, setType] = useState('');
  const [workflowState, setWorkflowState] = useState('');
  const [page, setPage] = useState(1);
  const [showCreate, setShowCreate] = useState(false);
  const [createTitle, setCreateTitle] = useState('');
  const [createType, setCreateType] = useState<EntityType>('person');
  const [createDomain, setCreateDomain] = useState('ahlulbayt');

  const filters = useMemo(
    () => ({
      q: query || undefined,
      type: type || undefined,
      workflow_state: (workflowState || undefined) as WorkflowState | undefined,
      page,
      sort: '-updated_at',
    }),
    [query, type, workflowState, page],
  );

  const entitiesQuery = useQuery({
    queryKey: ['editorial-entities', locale, filters],
    queryFn: () => getEditorialEntities(locale, filters),
  });

  const createMutation = useMutation({
    mutationFn: () =>
      createEntityDraft({
        entity_type: createType,
        domain: createDomain,
        locale,
        title: createTitle,
      }),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: ['editorial-entities'] });
      setShowCreate(false);
      setCreateTitle('');
      router.push(`/cms/entities/${response.data.uuid}`);
    },
  });

  const items = entitiesQuery.data?.data ?? [];
  const pagination = entitiesQuery.data?.meta?.pagination;

  return (
    <div className="flex flex-col gap-6">
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold text-[var(--color-ink)]">{t('entitiesTitle')}</h1>
          <p className="mt-1 text-[var(--color-ink-secondary)]">{t('entitiesSubtitle')}</p>
        </div>
        <Button type="button" onClick={() => setShowCreate((open) => !open)}>
          {t('createEntity')}
        </Button>
      </div>

      {showCreate ? (
        <div className="rounded-lg border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] p-4">
          <h2 className="mb-3 font-medium text-[var(--color-ink)]">{t('createEntityTitle')}</h2>
          <div className="grid gap-3 md:grid-cols-3">
            <Input
              label={t('fieldTitle')}
              value={createTitle}
              onChange={(e) => setCreateTitle(e.target.value)}
            />
            <label className="flex flex-col gap-1.5 text-sm">
              <span className="font-medium">{t('filterType')}</span>
              <select
                className="h-10 rounded-md border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] px-3"
                value={createType}
                onChange={(e) => setCreateType(e.target.value as EntityType)}
              >
                {ENTITY_TYPES.map((entityType) => (
                  <option key={entityType} value={entityType}>
                    {tTypes(entityType)}
                  </option>
                ))}
              </select>
            </label>
            <label className="flex flex-col gap-1.5 text-sm">
              <span className="font-medium">{t('editorDomains')}</span>
              <select
                className="h-10 rounded-md border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] px-3"
                value={createDomain}
                onChange={(e) => setCreateDomain(e.target.value)}
              >
                <option value="ahlulbayt">ahlulbayt</option>
                <option value="quran">quran</option>
                <option value="general">general</option>
                <option value="books_sources">books_sources</option>
              </select>
            </label>
          </div>
          <div className="mt-3 flex justify-end gap-2">
            <Button type="button" variant="secondary" onClick={() => setShowCreate(false)}>
              {t('cancel')}
            </Button>
            <Button
              type="button"
              loading={createMutation.isPending}
              disabled={!createTitle.trim()}
              onClick={() => createMutation.mutate()}
            >
              {t('createEntitySubmit')}
            </Button>
          </div>
        </div>
      ) : null}

      <div className="grid gap-3 md:grid-cols-4">
        <Input
          label={t('searchEntities')}
          value={query}
          onChange={(e) => {
            setQuery(e.target.value);
            setPage(1);
          }}
          placeholder={t('searchEntitiesPlaceholder')}
        />
        <label className="flex flex-col gap-1.5 text-sm">
          <span className="font-medium text-[var(--color-ink)]">{t('filterType')}</span>
          <select
            className="h-10 rounded-md border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] px-3"
            value={type}
            onChange={(e) => {
              setType(e.target.value);
              setPage(1);
            }}
          >
            <option value="">{t('allTypes')}</option>
            {ENTITY_TYPES.map((entityType) => (
              <option key={entityType} value={entityType}>
                {tTypes(entityType)}
              </option>
            ))}
          </select>
        </label>
        <label className="flex flex-col gap-1.5 text-sm">
          <span className="font-medium text-[var(--color-ink)]">{t('filterStatus')}</span>
          <select
            className="h-10 rounded-md border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] px-3"
            value={workflowState}
            onChange={(e) => {
              setWorkflowState(e.target.value);
              setPage(1);
            }}
          >
            <option value="">{t('allStatuses')}</option>
            {WORKFLOW_STATES.map((state) => (
              <option key={state} value={state}>
                {t(`workflow.${state}`)}
              </option>
            ))}
          </select>
        </label>
      </div>

      <div className="overflow-hidden rounded-xl border border-[var(--color-border-default)]">
        <table className="w-full text-sm">
          <thead className="bg-[var(--color-surface-sunken)] text-[var(--color-ink-secondary)]">
            <tr>
              <th className="px-4 py-3 text-start font-medium">{t('columnTitle')}</th>
              <th className="px-4 py-3 text-start font-medium">{t('columnType')}</th>
              <th className="px-4 py-3 text-start font-medium">{t('columnStatus')}</th>
              <th className="px-4 py-3 text-start font-medium">{t('columnUpdated')}</th>
            </tr>
          </thead>
          <tbody>
            {entitiesQuery.isLoading ? (
              <tr>
                <td colSpan={4} className="p-4">
                  <Skeleton className="h-16 w-full" />
                </td>
              </tr>
            ) : items.length === 0 ? (
              <tr>
                <td colSpan={4} className="p-8">
                  <EmptyState title={t('noEntities')} description={t('noEntitiesHint')} />
                </td>
              </tr>
            ) : (
              items.map((item) => (
                <tr key={item.uuid} className="border-t border-[var(--color-border-default)]">
                  <td className="px-4 py-3">
                    <Link
                      href={`/cms/entities/${item.uuid}`}
                      className="font-medium text-[var(--color-scholar)] hover:underline"
                    >
                      {item.title}
                    </Link>
                    <p className="text-xs text-[var(--color-ink-tertiary)]">{item.slug}</p>
                  </td>
                  <td className="px-4 py-3">
                    <Badge variant={entityBadgeVariant(item.entity_type)}>{tTypes(item.entity_type)}</Badge>
                  </td>
                  <td className="px-4 py-3">
                    <WorkflowBadge state={item.workflow_state} />
                  </td>
                  <td className="px-4 py-3 text-[var(--color-ink-secondary)]">
                    {item.updated_at ? new Date(item.updated_at).toLocaleString(locale) : '—'}
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {pagination && pagination.last_page > 1 ? (
        <div className="flex items-center justify-end gap-2">
          <Button
            variant="secondary"
            size="sm"
            disabled={page <= 1}
            onClick={() => setPage((p) => Math.max(1, p - 1))}
          >
            {t('prevPage')}
          </Button>
          <span className="text-sm text-[var(--color-ink-secondary)]">
            {page} / {pagination.last_page}
          </span>
          <Button
            variant="secondary"
            size="sm"
            disabled={page >= pagination.last_page}
            onClick={() => setPage((p) => p + 1)}
          >
            {t('nextPage')}
          </Button>
        </div>
      ) : null}
    </div>
  );
}
