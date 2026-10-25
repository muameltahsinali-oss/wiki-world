import { cva, type VariantProps } from 'class-variance-authority';
import * as React from 'react';
import { cn } from '@/lib/utils';

const badgeVariants = cva(
  'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
  {
    variants: {
      variant: {
        default: 'bg-[var(--color-scholar-subtle)] text-[var(--color-scholar)]',
        success: 'bg-[color-mix(in_srgb,var(--color-success)_12%,transparent)] text-[var(--color-success)]',
        warning: 'bg-[color-mix(in_srgb,var(--color-warning)_12%,transparent)] text-[var(--color-warning)]',
        error: 'bg-[color-mix(in_srgb,var(--color-error)_12%,transparent)] text-[var(--color-error)]',
        info: 'bg-[color-mix(in_srgb,var(--color-info)_12%,transparent)] text-[var(--color-info)]',
        person: 'bg-[color-mix(in_srgb,var(--color-entity-person)_12%,transparent)] text-[var(--color-entity-person)]',
        event: 'bg-[color-mix(in_srgb,var(--color-entity-event)_12%,transparent)] text-[var(--color-entity-event)]',
        place: 'bg-[color-mix(in_srgb,var(--color-entity-place)_12%,transparent)] text-[var(--color-entity-place)]',
        concept: 'bg-[color-mix(in_srgb,var(--color-entity-concept)_12%,transparent)] text-[var(--color-entity-concept)]',
        source: 'bg-[color-mix(in_srgb,var(--color-entity-source)_12%,transparent)] text-[var(--color-entity-source)]',
        book: 'bg-[color-mix(in_srgb,var(--color-entity-book)_12%,transparent)] text-[var(--color-entity-book)]',
        article: 'bg-[color-mix(in_srgb,var(--color-entity-article)_12%,transparent)] text-[var(--color-entity-article)]',
        verse: 'bg-[color-mix(in_srgb,var(--color-entity-verse)_12%,transparent)] text-[var(--color-entity-verse)]',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export interface BadgeProps
  extends React.HTMLAttributes<HTMLSpanElement>,
    VariantProps<typeof badgeVariants> {}

export function Badge({ className, variant, ...props }: BadgeProps) {
  return <span className={cn(badgeVariants({ variant, className }))} {...props} />;
}

export type EntityType =
  | 'person'
  | 'event'
  | 'place'
  | 'concept'
  | 'source'
  | 'book'
  | 'article'
  | 'verse';

export function entityBadgeVariant(type: EntityType): NonNullable<BadgeProps['variant']> {
  return type;
}
