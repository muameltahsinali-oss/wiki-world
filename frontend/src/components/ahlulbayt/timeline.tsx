'use client';

import { useLocale, useTranslations } from 'next-intl';
import { useQuery } from '@tanstack/react-query';
import { Link } from '@/i18n/navigation';
import { getAhlulbaytTimeline, type TimelineEvent } from '@/lib/api/ahlulbayt';
import { entityPublicHref } from '@/lib/entity/types';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

export interface TimelineProps {
  module?: string;
  className?: string;
  compact?: boolean;
}

export function Timeline({ module = 'ahlulbayt', className, compact = false }: TimelineProps) {
  const locale = useLocale();
  const t = useTranslations('ahlulbayt.timeline');

  const timelineQuery = useQuery({
    queryKey: ['timeline', locale, module],
    queryFn: () => getAhlulbaytTimeline(locale, { module, limit: compact ? 5 : 50 }),
  });

  const events = timelineQuery.data?.data ?? [];

  if (timelineQuery.isLoading) {
    return <div className={cn('h-48 animate-pulse rounded-lg bg-[var(--color-surface-sunken)]', className)} />;
  }

  if (events.length === 0) {
    return (
      <p className={cn('text-sm text-[var(--color-ink-secondary)]', className)}>{t('empty')}</p>
    );
  }

  return (
    <div className={cn('relative', className)}>
      <div className="absolute inset-y-0 start-4 w-px bg-[var(--color-border-strong)]" aria-hidden />
      <ol className="flex flex-col gap-6">
        {events.map((event) => (
          <TimelineItem key={event.uuid} event={event} compact={compact} />
        ))}
      </ol>
    </div>
  );
}

function TimelineItem({ event, compact }: { event: TimelineEvent; compact?: boolean }) {
  const t = useTranslations('ahlulbayt.timeline');

  return (
    <li className="relative ps-10">
      <span
        className="absolute start-2.5 top-1.5 h-3 w-3 rounded-full border-2 border-[var(--color-scholar)] bg-[var(--color-surface-base)]"
        aria-hidden
      />
      <div className="flex flex-col gap-2">
        <div className="flex flex-wrap items-center gap-2">
          {event.start_date_hijri ? (
            <Badge variant="default">{event.start_date_hijri} {t('hijri')}</Badge>
          ) : null}
          {event.era ? <Badge variant="info">{event.era}</Badge> : null}
        </div>
        <Link
          href={entityPublicHref(event.type, event.uuid)}
          className="text-base font-semibold text-[var(--color-ink)] hover:text-[var(--color-scholar)]"
        >
          {event.title}
        </Link>
        {!compact && event.summary ? (
          <p className="text-sm text-[var(--color-ink-secondary)]">{event.summary}</p>
        ) : null}
        {event.place ? (
          <p className="text-xs text-[var(--color-ink-tertiary)]">
            {t('place')}: {event.place.title}
          </p>
        ) : null}
      </div>
    </li>
  );
}
