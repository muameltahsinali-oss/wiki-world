'use client';

import type { ReactNode } from 'react';
import { ReadingProgressBar } from '@/components/entity/reading-progress-bar';
import { StickyToc } from '@/components/entity/sticky-toc';
import { cn } from '@/lib/utils';

export interface TocHeading {
  id: string;
  text: string;
  level: number;
}

export interface EntityPageLayoutProps {
  title: string;
  summary?: string | null;
  meta?: ReactNode;
  actions?: ReactNode;
  infobox?: ReactNode;
  toc?: TocHeading[];
  showReadingProgress?: boolean;
  children: ReactNode;
  footer?: ReactNode;
  className?: string;
}

export function EntityPageLayout({
  title,
  summary,
  meta,
  actions,
  infobox,
  toc,
  showReadingProgress = false,
  children,
  footer,
  className,
}: EntityPageLayoutProps) {
  const hasToc = toc && toc.length > 0;

  return (
    <>
      {showReadingProgress ? <ReadingProgressBar /> : null}
      <article
        className={cn('mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:py-12', className)}
        data-entity-layout
      >
        <header className="mb-8 flex flex-col gap-4 border-b border-[var(--color-border-default)] pb-8">
          <div className="flex flex-wrap items-start justify-between gap-4">
            <div className="flex min-w-0 flex-1 flex-col gap-3">
              <h1 className="text-[length:var(--text-h1)] font-bold leading-tight text-[var(--color-ink)]">
                {title}
              </h1>
              {summary ? (
                <p className="max-w-3xl text-lg text-[var(--color-ink-secondary)]">{summary}</p>
              ) : null}
              {meta ? <div className="flex flex-wrap items-center gap-3 text-sm">{meta}</div> : null}
            </div>
            {actions ? <div className="flex shrink-0 items-center gap-2">{actions}</div> : null}
          </div>
        </header>

        <div
          className={cn(
            'grid gap-8',
            infobox || hasToc ? 'lg:grid-cols-[minmax(0,1fr)_280px]' : 'grid-cols-1',
          )}
        >
          <div className="min-w-0" data-entity-content>
            {children}
            {footer ? <div className="mt-10 border-t border-[var(--color-border-default)] pt-8">{footer}</div> : null}
          </div>

          {(infobox || hasToc) && (
            <aside className="flex flex-col gap-6 lg:sticky lg:top-[calc(var(--header-height)+1rem)] lg:self-start">
              {hasToc ? <StickyToc headings={toc} /> : null}
              {infobox}
            </aside>
          )}
        </div>
      </article>
    </>
  );
}
