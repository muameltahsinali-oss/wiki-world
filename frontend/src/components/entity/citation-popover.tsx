'use client';

import { useState, type ReactNode } from 'react';
import { useLocale, useTranslations } from 'next-intl';
import { useQuery } from '@tanstack/react-query';
import { Dialog } from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';
import { Link } from '@/i18n/navigation';
import { getPublicEntityByType } from '@/lib/api/public';
import { entityPublicHref } from '@/lib/entity/types';
import { ApiClientError } from '@/lib/api/client';

export interface CitationPopoverProps {
  sourceUuid?: string;
  sourceTitle: string;
  reference?: string;
  children: ReactNode;
}

export function CitationPopover({
  sourceUuid,
  sourceTitle,
  reference,
  children,
}: CitationPopoverProps) {
  const t = useTranslations('entity.citation');
  const locale = useLocale();
  const [open, setOpen] = useState(false);

  const sourceQuery = useQuery({
    queryKey: ['public-source-preview', sourceUuid, locale],
    queryFn: () => getPublicEntityByType(locale, 'source', sourceUuid!),
    enabled: open && Boolean(sourceUuid),
    retry: false,
  });

  const source = sourceQuery.data?.data;

  return (
    <>
      <button
        type="button"
        className="w-full cursor-pointer text-start"
        onClick={() => setOpen(true)}
        aria-label={t('openPreview', { title: sourceTitle })}
      >
        {children}
      </button>

      <Dialog open={open} onOpenChange={setOpen} title={sourceTitle}>
        <div className="flex flex-col gap-3">
          {reference ? (
            <p className="text-sm text-[var(--color-ink-tertiary)]">{reference}</p>
          ) : null}
          {sourceQuery.isLoading ? <Skeleton className="h-20 w-full" /> : null}
          {sourceQuery.error instanceof ApiClientError && sourceQuery.error.status === 404 ? (
            <p className="text-sm text-[var(--color-ink-secondary)]">{t('unavailable')}</p>
          ) : null}
          {source ? (
            <>
              {source.summary ? (
                <p className="text-sm text-[var(--color-ink-secondary)]">{source.summary}</p>
              ) : null}
              {sourceUuid ? (
                <Link
                  href={entityPublicHref('source', sourceUuid)}
                  className="text-sm font-medium text-[var(--color-scholar)] hover:underline"
                >
                  {t('viewSource')}
                </Link>
              ) : null}
            </>
          ) : null}
          {!sourceUuid ? (
            <p className="text-sm text-[var(--color-ink-secondary)]">{t('noSourceLinked')}</p>
          ) : null}
        </div>
      </Dialog>
    </>
  );
}
