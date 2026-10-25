'use client';

import { useQuery } from '@tanstack/react-query';
import { useTranslations } from 'next-intl';
import { Skeleton } from '@/components/ui/skeleton';
import { getEntityPreview } from '@/lib/api/editorial';

export function PreviewPanel({ uuid, locale }: { uuid: string; locale: string }) {
  const t = useTranslations('cms.preview');

  const previewQuery = useQuery({
    queryKey: ['entity-preview', uuid, locale],
    queryFn: () => getEntityPreview(uuid, locale),
  });

  const data = previewQuery.data?.data;

  if (previewQuery.isLoading) {
    return <Skeleton className="h-64 w-full rounded-lg" />;
  }

  if (!data) {
    return <p className="text-sm text-[var(--color-ink-secondary)]">{t('unavailable')}</p>;
  }

  return (
    <div className="flex flex-col gap-3 rounded-lg border border-[var(--color-border-default)] bg-[var(--color-surface-base)] p-4">
      <h3 className="text-lg font-semibold text-[var(--color-ink)]">{String(data.title ?? '')}</h3>
      {data.summary ? (
        <p className="text-sm text-[var(--color-ink-secondary)]">{String(data.summary)}</p>
      ) : null}
      <div className="prose prose-sm max-w-none text-[var(--color-ink)]">
        {Array.isArray(data.blocks)
          ? data.blocks.map((block: { type?: string; content?: unknown }, index: number) => (
              <div key={index} className="mb-2 text-sm">
                <span className="text-xs uppercase text-[var(--color-ink-tertiary)]">
                  {block.type}
                </span>
                <pre className="mt-1 whitespace-pre-wrap rounded bg-[var(--color-surface-sunken)] p-2 text-xs">
                  {JSON.stringify(block.content, null, 2)}
                </pre>
              </div>
            ))
          : null}
      </div>
    </div>
  );
}
