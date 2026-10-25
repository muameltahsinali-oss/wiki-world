import { Badge, entityBadgeVariant, type EntityType } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { Link } from '@/i18n/navigation';
import { cn } from '@/lib/utils';

export interface EntityCardProps {
  uuid: string;
  title: string;
  summary?: string;
  entityType: EntityType;
  typeLabel: string;
  href: string;
  size?: 'sm' | 'md' | 'lg';
  className?: string;
}

const sizeClasses = {
  sm: 'p-3 gap-2',
  md: 'p-4 gap-3',
  lg: 'p-6 gap-4',
} as const;

const titleClasses = {
  sm: 'text-sm font-medium',
  md: 'text-base font-semibold',
  lg: 'text-lg font-semibold',
} as const;

export function EntityCard({
  title,
  summary,
  entityType,
  typeLabel,
  href,
  size = 'md',
  className,
}: EntityCardProps) {
  return (
    <Card
      variant="interactive"
      padding="none"
      className={cn('flex flex-col', sizeClasses[size], className)}
    >
      <Link href={href} className="flex flex-col gap-2 outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-scholar)]">
        <Badge variant={entityBadgeVariant(entityType)}>{typeLabel}</Badge>
        <h3 className={cn('text-[var(--color-ink)]', titleClasses[size])}>{title}</h3>
        {summary ? (
          <p className="line-clamp-2 text-sm text-[var(--color-ink-secondary)]">{summary}</p>
        ) : null}
      </Link>
    </Card>
  );
}
