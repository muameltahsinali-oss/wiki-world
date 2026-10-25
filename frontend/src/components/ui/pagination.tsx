'use client';

import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useTranslations } from 'next-intl';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export interface PaginationProps {
  page: number;
  totalPages: number;
  onPageChange: (page: number) => void;
  variant?: 'offset' | 'cursor';
  hasNextPage?: boolean;
  hasPreviousPage?: boolean;
  className?: string;
}

export function Pagination({
  page,
  totalPages,
  onPageChange,
  variant = 'offset',
  hasNextPage,
  hasPreviousPage,
  className,
}: PaginationProps) {
  const t = useTranslations('pagination');

  const canGoPrevious = variant === 'cursor' ? (hasPreviousPage ?? page > 1) : page > 1;
  const canGoNext =
    variant === 'cursor' ? (hasNextPage ?? false) : page < totalPages;

  return (
    <nav
      aria-label={t('label')}
      className={cn('flex items-center justify-between gap-4', className)}
    >
      <Button
        type="button"
        variant="secondary"
        size="sm"
        disabled={!canGoPrevious}
        onClick={() => onPageChange(page - 1)}
        aria-label={t('previous')}
      >
        <ChevronLeft className="h-4 w-4 rtl:rotate-180" aria-hidden />
        {t('previous')}
      </Button>

      {variant === 'offset' ? (
        <span className="text-sm text-[var(--color-ink-secondary)]">
          {t('pageOf', { page, total: totalPages })}
        </span>
      ) : (
        <span className="text-sm text-[var(--color-ink-secondary)]">{t('cursorHint')}</span>
      )}

      <Button
        type="button"
        variant="secondary"
        size="sm"
        disabled={!canGoNext}
        onClick={() => onPageChange(page + 1)}
        aria-label={t('next')}
      >
        {t('next')}
        <ChevronRight className="h-4 w-4 rtl:rotate-180" aria-hidden />
      </Button>
    </nav>
  );
}
