'use client';

import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useTranslations } from 'next-intl';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  createEntityRelationship,
  deleteEntityRelationship,
  getEntityRelationships,
  searchEditorialEntities,
} from '@/lib/api/editorial';
import * as React from 'react';

export function RelationshipEditorPanel({ uuid, locale }: { uuid: string; locale: string }) {
  const t = useTranslations('cms.relationships');
  const queryClient = useQueryClient();
  const [query, setQuery] = React.useState('');
  const [relationshipType, setRelationshipType] = React.useState('related_to');
  const [selected, setSelected] = React.useState<{ uuid: string; title: string } | null>(null);

  const relationshipsQuery = useQuery({
    queryKey: ['entity-relationships', uuid, locale],
    queryFn: () => getEntityRelationships(uuid, locale),
  });

  const searchQuery = useQuery({
    queryKey: ['relationship-search', locale, query],
    queryFn: () => searchEditorialEntities(query, locale, 1),
    enabled: query.length >= 2,
  });

  const addMutation = useMutation({
    mutationFn: () =>
      createEntityRelationship(
        uuid,
        { target_entity_uuid: selected!.uuid, relationship_type: relationshipType },
        locale,
      ),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['entity-relationships', uuid] });
      setSelected(null);
      setQuery('');
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => deleteEntityRelationship(uuid, id, locale),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['entity-relationships', uuid] }),
  });

  const relationships = relationshipsQuery.data?.data ?? [];
  const results = searchQuery.data?.data ?? [];

  return (
    <div className="flex flex-col gap-4">
      <h3 className="text-sm font-semibold text-[var(--color-ink)]">{t('title')}</h3>
      <ul className="flex flex-col gap-2">
        {relationships.map((rel) => (
          <li
            key={rel.id}
            className="flex items-center justify-between gap-2 rounded-lg border border-[var(--color-border-default)] p-3 text-sm"
          >
            <div>
              <p className="font-medium">{rel.relationship_type}</p>
              <p className="text-xs text-[var(--color-ink-tertiary)]">
                {rel.peer_entity.uuid} ({rel.direction})
              </p>
            </div>
            <Button
              type="button"
              variant="ghost"
              size="sm"
              onClick={() => deleteMutation.mutate(rel.id)}
            >
              {t('remove')}
            </Button>
          </li>
        ))}
      </ul>
      <Input
        label={t('relationshipType')}
        value={relationshipType}
        onChange={(e) => setRelationshipType(e.target.value)}
      />
      <Input label={t('searchEntity')} value={query} onChange={(e) => setQuery(e.target.value)} />
      {results.length > 0 ? (
        <ul className="max-h-32 overflow-auto rounded-md border">
          {results.map((item) => (
            <li key={item.uuid}>
              <button
                type="button"
                className="w-full px-3 py-2 text-start text-sm hover:bg-[var(--color-surface-sunken)]"
                onClick={() => setSelected({ uuid: item.uuid, title: item.title })}
              >
                {item.title}
              </button>
            </li>
          ))}
        </ul>
      ) : null}
      {selected ? (
        <p className="text-xs text-[var(--color-ink-secondary)]">
          {t('selected')}: {selected.title}
        </p>
      ) : null}
      <Button
        type="button"
        size="sm"
        disabled={!selected || addMutation.isPending}
        onClick={() => addMutation.mutate()}
      >
        {t('add')}
      </Button>
    </div>
  );
}
