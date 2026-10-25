'use client';

import { useTranslations } from 'next-intl';
import { EntityCard } from '@/components/ui/entity-card';
import type { EntityType } from '@/components/ui/badge';
import { entityPublicHref } from '@/lib/entity/types';
import type { PublicRelationship } from '@/lib/entity/types';
import { useEntityTypeLabel } from '@/lib/entity/person-label';
import { cn } from '@/lib/utils';

export interface RelatedEntitiesPanelProps {
  relationships: PublicRelationship[];
  className?: string;
}

export function RelatedEntitiesPanel({ relationships, className }: RelatedEntitiesPanelProps) {
  const t = useTranslations('entity.related');
  const entityTypeLabel = useEntityTypeLabel();

  if (relationships.length === 0) return null;

  return (
    <section className={cn('flex flex-col gap-4', className)} aria-label={t('title')}>
      <h2 className="text-lg font-semibold text-[var(--color-ink)]">{t('title')}</h2>
      <div className="flex gap-4 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        {relationships.map((relationship) => {
          const peer = relationship.peer_entity;
          const type = peer.entity_type as EntityType;
          const title = peer.title ?? `${entityTypeLabel(type, { title: peer.title })} (${peer.uuid.slice(0, 8)}…)`;

          return (
            <EntityCard
              key={relationship.id}
              uuid={peer.uuid}
              title={title}
              entityType={type}
              typeLabel={entityTypeLabel(type, { title: peer.title, module: 'ahlulbayt' })}
              href={entityPublicHref(type, peer.uuid)}
              size="sm"
              className="min-w-[220px] shrink-0"
            />
          );
        })}
      </div>
    </section>
  );
}
