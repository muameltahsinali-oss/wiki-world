'use client';

import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useTranslations } from 'next-intl';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  createEntityReference,
  deleteEntityReference,
  getEntityReferences,
  searchEditorialEntities,
} from '@/lib/api/editorial';
import * as React from 'react';

export function ReferenceEditorPanel({ uuid, locale }: { uuid: string; locale: string }) {
  const t = useTranslations('cms.references');
  const queryClient = useQueryClient();
  const [sourceQuery, setSourceQuery] = React.useState('');
  const [selectedSource, setSelectedSource] = React.useState<{ uuid: string; title: string } | null>(
    null,
  );
  const [pageRef, setPageRef] = React.useState('');
  const [excerpt, setExcerpt] = React.useState('');

  const referencesQuery = useQuery({
    queryKey: ['entity-references', uuid, locale],
    queryFn: () => getEntityReferences(uuid, locale),
  });

  const searchQuery = useQuery({
    queryKey: ['source-search', locale, sourceQuery],
    queryFn: () => searchEditorialEntities(sourceQuery, locale, 1),
    enabled: sourceQuery.length >= 2,
  });

  const addMutation = useMutation({
    mutationFn: () =>
      createEntityReference(
        uuid,
        {
          source_entity_uuid: selectedSource!.uuid,
          page_reference: pageRef || undefined,
          quote_excerpt: excerpt || undefined,
        },
        locale,
      ),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['entity-references', uuid] });
      setSelectedSource(null);
      setSourceQuery('');
      setPageRef('');
      setExcerpt('');
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => deleteEntityReference(uuid, id, locale),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['entity-references', uuid] }),
  });

  const sources = (searchQuery.data?.data ?? []).filter((e) => e.entity_type === 'source');
  const references = referencesQuery.data?.data ?? [];

  return (
    <div className="flex flex-col gap-4">
      <h3 className="text-sm font-semibold text-[var(--color-ink)]">{t('title')}</h3>
      <ul className="flex flex-col gap-2">
        {references.map((ref) => (
          <li
            key={ref.id}
            className="flex items-start justify-between gap-2 rounded-lg border border-[var(--color-border-default)] p-3 text-sm"
          >
            <div>
              <p className="font-medium">{ref.source_uuid}</p>
              {ref.page_reference ? (
                <p className="text-xs text-[var(--color-ink-tertiary)]">{ref.page_reference}</p>
              ) : null}
            </div>
            <Button
              type="button"
              variant="ghost"
              size="sm"
              onClick={() => deleteMutation.mutate(ref.id)}
            >
              {t('remove')}
            </Button>
          </li>
        ))}
      </ul>
      <Input
        label={t('searchSource')}
        value={sourceQuery}
        onChange={(e) => setSourceQuery(e.target.value)}
      />
      {sources.length > 0 ? (
        <ul className="max-h-32 overflow-auto rounded-md border">
          {sources.map((item) => (
            <li key={item.uuid}>
              <button
                type="button"
                className="w-full px-3 py-2 text-start text-sm hover:bg-[var(--color-surface-sunken)]"
                onClick={() => setSelectedSource({ uuid: item.uuid, title: item.title })}
              >
                {item.title}
              </button>
            </li>
          ))}
        </ul>
      ) : null}
      {selectedSource ? (
        <p className="text-xs text-[var(--color-ink-secondary)]">
          {t('selected')}: {selectedSource.title}
        </p>
      ) : null}
      <Input label={t('pageReference')} value={pageRef} onChange={(e) => setPageRef(e.target.value)} />
      <label className="flex flex-col gap-1 text-sm">
        <span>{t('excerpt')}</span>
        <textarea
          className="min-h-20 rounded-md border border-[var(--color-border-default)] p-3"
          value={excerpt}
          onChange={(e) => setExcerpt(e.target.value)}
        />
      </label>
      <Button
        type="button"
        size="sm"
        disabled={!selectedSource || addMutation.isPending}
        onClick={() => addMutation.mutate()}
      >
        {t('add')}
      </Button>
    </div>
  );
}
