'use client';

import { useState } from 'react';
import { useLocale, useTranslations } from 'next-intl';
import { useQuery } from '@tanstack/react-query';
import { ChevronDown, ChevronRight } from 'lucide-react';
import { Link } from '@/i18n/navigation';
import { getGenealogy, type GenealogyNode } from '@/lib/api/ahlulbayt';
import { entityPublicHref } from '@/lib/entity/types';
import { cn } from '@/lib/utils';

function GenealogyTreeNode({ node, depth = 0 }: { node: GenealogyNode; depth?: number }) {
  const [expanded, setExpanded] = useState(depth < 2);
  const hasChildren = Boolean(node.children?.length);

  return (
    <li className="flex flex-col gap-1">
      <div className="flex items-center gap-2" style={{ paddingInlineStart: `${depth}rem` }}>
        {hasChildren ? (
          <button
            type="button"
            onClick={() => setExpanded((value) => !value)}
            className="rounded p-0.5 text-[var(--color-ink-tertiary)] hover:bg-[var(--color-surface-sunken)]"
            aria-expanded={expanded}
          >
            {expanded ? <ChevronDown className="h-4 w-4" /> : <ChevronRight className="h-4 w-4 rtl:rotate-180" />}
          </button>
        ) : (
          <span className="w-5" />
        )}
        <Link
          href={entityPublicHref(node.entity_type, node.uuid)}
          className={cn(
            'text-sm hover:text-[var(--color-scholar)] hover:underline',
            node.is_masum ? 'font-semibold text-[var(--color-ink)]' : 'text-[var(--color-ink-secondary)]',
          )}
        >
          {node.title}
          {node.imam_number ? ` (${node.imam_number})` : ''}
        </Link>
      </div>
      {hasChildren && expanded ? (
        <ul className="flex flex-col gap-1">
          {node.children!.map((child) => (
            <GenealogyTreeNode key={child.uuid} node={child} depth={depth + 1} />
          ))}
        </ul>
      ) : null}
    </li>
  );
}

export function GenealogyTree({ className }: { className?: string }) {
  const locale = useLocale();
  const t = useTranslations('ahlulbayt.genealogy');

  const genealogyQuery = useQuery({
    queryKey: ['ahlulbayt-genealogy', locale],
    queryFn: () => getGenealogy(locale),
  });

  const root = genealogyQuery.data?.data;

  if (genealogyQuery.isLoading) {
    return <div className={cn('h-48 animate-pulse rounded-lg bg-[var(--color-surface-sunken)]', className)} />;
  }

  if (!root) {
    return (
      <p className={cn('text-sm text-[var(--color-ink-secondary)]', className)}>{t('unavailable')}</p>
    );
  }

  return (
    <section className={cn('rounded-lg border border-[var(--color-border-default)] p-4', className)}>
      <h2 className="mb-4 text-lg font-semibold text-[var(--color-ink)]">{t('title')}</h2>
      <ul className="flex flex-col gap-2">
        <GenealogyTreeNode node={root} />
      </ul>
    </section>
  );
}
