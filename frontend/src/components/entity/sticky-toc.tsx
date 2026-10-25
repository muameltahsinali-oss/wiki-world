'use client';

import { useEffect, useState } from 'react';
import { useTranslations } from 'next-intl';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { TocHeading } from '@/components/entity/entity-page-layout';
import { cn } from '@/lib/utils';

export interface StickyTocProps {
  headings: TocHeading[];
  className?: string;
}

export function StickyToc({ headings, className }: StickyTocProps) {
  const t = useTranslations('entity.toc');
  const [activeId, setActiveId] = useState<string | null>(headings[0]?.id ?? null);

  useEffect(() => {
    if (headings.length === 0) return;

    const observer = new IntersectionObserver(
      (entries) => {
        const visible = entries
          .filter((entry) => entry.isIntersecting)
          .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
        if (visible[0]?.target.id) {
          setActiveId(visible[0].target.id);
        }
      },
      { rootMargin: '-20% 0px -70% 0px', threshold: 0 },
    );

    for (const heading of headings) {
      const element = document.getElementById(heading.id);
      if (element) observer.observe(element);
    }

    return () => observer.disconnect();
  }, [headings]);

  if (headings.length === 0) return null;

  return (
    <Card className={cn('hidden lg:block', className)}>
      <CardHeader className="py-3">
        <CardTitle className="text-sm font-semibold text-[var(--color-ink)]">{t('title')}</CardTitle>
      </CardHeader>
      <CardContent className="pb-4">
        <nav aria-label={t('title')}>
          <ol className="flex flex-col gap-1">
            {headings.map((heading) => (
              <li key={heading.id} style={{ paddingInlineStart: `${(heading.level - 2) * 0.75}rem` }}>
                <a
                  href={`#${heading.id}`}
                  className={cn(
                    'block rounded px-2 py-1 text-sm transition-colors hover:bg-[var(--color-surface-sunken)]',
                    activeId === heading.id
                      ? 'font-medium text-[var(--color-scholar)]'
                      : 'text-[var(--color-ink-secondary)]',
                  )}
                  onClick={(event) => {
                    event.preventDefault();
                    document.getElementById(heading.id)?.scrollIntoView({ behavior: 'smooth' });
                    setActiveId(heading.id);
                  }}
                >
                  {heading.text}
                </a>
              </li>
            ))}
          </ol>
        </nav>
      </CardContent>
    </Card>
  );
}
