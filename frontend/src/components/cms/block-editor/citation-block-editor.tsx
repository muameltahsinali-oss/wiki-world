'use client';

import * as React from 'react';
import { useQuery } from '@tanstack/react-query';
import { useTranslations } from 'next-intl';
import { Input } from '@/components/ui/input';
import { searchEditorialEntities } from '@/lib/api/editorial';
import type { ContentBlock } from '@/lib/cms/types';

export function CitationBlockEditor({
  block,
  locale,
  onChange,
}: {
  block: ContentBlock;
  locale: string;
  onChange: (block: ContentBlock) => void;
}) {
  const t = useTranslations('cms.blockEditor');
  const [query, setQuery] = React.useState(block.content.source_title ?? '');

  const searchQuery = useQuery({
    queryKey: ['entity-search-source', locale, query],
    queryFn: () => searchEditorialEntities(query, locale, 1),
    enabled: query.length >= 2,
  });

  const results = (searchQuery.data?.data ?? []).filter((item) => item.entity_type === 'source');

  return (
    <div className="flex flex-col gap-3">
      <Input
        label={t('searchSource')}
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        placeholder={t('searchSourcePlaceholder')}
      />
      {results.length > 0 ? (
        <ul className="max-h-40 overflow-auto rounded-md border border-[var(--color-border-default)]">
          {results.map((item) => (
            <li key={item.uuid}>
              <button
                type="button"
                className="w-full px-3 py-2 text-start text-sm hover:bg-[var(--color-surface-sunken)]"
                onClick={() => {
                  onChange({
                    ...block,
                    content: {
                      ...block.content,
                      source_uuid: item.uuid,
                      source_title: item.title,
                    },
                  });
                  setQuery(item.title);
                }}
              >
                {item.title}
              </button>
            </li>
          ))}
        </ul>
      ) : null}
      <Input
        label={t('referenceLocator')}
        value={block.content.reference ?? ''}
        onChange={(e) =>
          onChange({ ...block, content: { ...block.content, reference: e.target.value } })
        }
      />
      {block.content.source_title ? (
        <p className="text-xs text-[var(--color-ink-secondary)]">
          {t('selectedSource')}: {block.content.source_title}
        </p>
      ) : null}
    </div>
  );
}
