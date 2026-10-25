import { BookOpen } from 'lucide-react';
import { cn } from '@/lib/utils';

export interface CitationBlockProps {
  sourceTitle: string;
  excerpt?: string;
  reference?: string;
  href?: string;
  className?: string;
}

export function CitationBlock({
  sourceTitle,
  excerpt,
  reference,
  href,
  className,
}: CitationBlockProps) {
  const content = (
    <>
      <div className="flex items-start gap-2">
        <BookOpen className="mt-0.5 h-4 w-4 shrink-0 text-[var(--color-wisdom)]" aria-hidden />
        <div className="flex flex-col gap-1">
          <p className="text-sm font-medium text-[var(--color-ink)]">{sourceTitle}</p>
          {reference ? (
            <p className="text-xs text-[var(--color-ink-tertiary)]">{reference}</p>
          ) : null}
        </div>
      </div>
      {excerpt ? (
        <blockquote className="border-s-2 border-[var(--color-wisdom)] ps-3 text-sm italic text-[var(--color-ink-secondary)]">
          {excerpt}
        </blockquote>
      ) : null}
    </>
  );

  const baseClass = cn(
    'flex flex-col gap-3 rounded-lg border border-[var(--color-border-default)] bg-[var(--color-wisdom-subtle)] p-4',
    className,
  );

  if (href) {
    return (
      <a href={href} className={cn(baseClass, 'transition-colors hover:border-[var(--color-border-strong)]')}>
        {content}
      </a>
    );
  }

  return <aside className={baseClass}>{content}</aside>;
}
