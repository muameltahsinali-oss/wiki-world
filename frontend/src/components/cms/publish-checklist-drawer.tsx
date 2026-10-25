'use client';

import { useQuery } from '@tanstack/react-query';
import { CheckCircle2, XCircle } from 'lucide-react';
import { useTranslations } from 'next-intl';
import { Drawer } from '@/components/ui/drawer';
import { getPublishReadiness } from '@/lib/api/editorial';
import type { PublishCheck } from '@/lib/cms/types';
import { cn } from '@/lib/utils';

export function PublishChecklistDrawer({
  uuid,
  locale,
  open,
  onOpenChange,
}: {
  uuid: string;
  locale: string;
  open: boolean;
  onOpenChange: (open: boolean) => void;
}) {
  const t = useTranslations('cms.publishChecklist');

  const readinessQuery = useQuery({
    queryKey: ['publish-readiness', uuid, locale],
    queryFn: () => getPublishReadiness(uuid, locale),
    enabled: open,
  });

  const checks = readinessQuery.data?.data.checks ?? [];
  const ready = readinessQuery.data?.data.ready ?? false;

  return (
    <Drawer open={open} onOpenChange={onOpenChange} title={t('title')}>
      <div className="flex flex-col gap-4">
        <p
          className={cn(
            'rounded-lg px-3 py-2 text-sm font-medium',
            ready
              ? 'bg-[color-mix(in_srgb,var(--color-success)_12%,transparent)] text-[var(--color-success)]'
              : 'bg-[color-mix(in_srgb,var(--color-warning)_12%,transparent)] text-[var(--color-warning)]',
          )}
        >
          {ready ? t('ready') : t('notReady')}
        </p>
        <ul className="flex flex-col gap-3">
          {checks.map((check) => (
            <CheckItem key={check.code} check={check} />
          ))}
        </ul>
      </div>
    </Drawer>
  );
}

function CheckItem({ check }: { check: PublishCheck }) {
  const t = useTranslations('cms.publishChecklist');
  const Icon = check.passed ? CheckCircle2 : XCircle;

  return (
    <li className="flex items-start gap-3 rounded-lg border border-[var(--color-border-default)] p-3">
      <Icon
        className={cn(
          'mt-0.5 h-5 w-5 shrink-0',
          check.passed ? 'text-[var(--color-success)]' : 'text-[var(--color-error)]',
        )}
        aria-hidden
      />
      <div>
        <p className="text-sm font-medium text-[var(--color-ink)]">{t(`checks.${check.code}`)}</p>
        {!check.passed && check.message ? (
          <p className="mt-1 text-xs text-[var(--color-ink-secondary)]">{check.message}</p>
        ) : null}
        {check.required !== undefined ? (
          <p className="mt-1 text-xs text-[var(--color-ink-tertiary)]">
            {check.actual ?? 0} / {check.required}
          </p>
        ) : null}
      </div>
    </li>
  );
}
