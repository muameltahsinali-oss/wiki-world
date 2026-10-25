'use client';

import { useLocale, useTranslations } from 'next-intl';
import { useQuery } from '@tanstack/react-query';
import { Link } from '@/i18n/navigation';
import { getPersonLineage } from '@/lib/api/ahlulbayt';
import { entityPublicHref } from '@/lib/entity/types';
import type { EntityType } from '@/components/ui/badge';

export function LineagePanel({ uuid }: { uuid: string }) {
  const locale = useLocale();
  const t = useTranslations('ahlulbayt.lineage');

  const lineageQuery = useQuery({
    queryKey: ['person-lineage', uuid, locale],
    queryFn: () => getPersonLineage(locale, uuid),
    retry: false,
  });

  const data = lineageQuery.data?.data;
  if (!data || (data.ancestors.length === 0 && data.descendants.length === 0)) {
    return null;
  }

  return (
    <section className="flex flex-col gap-4 rounded-lg border border-[var(--color-border-default)] p-4">
      <h2 className="text-lg font-semibold text-[var(--color-ink)]">{t('title')}</h2>
      {data.ancestors.length > 0 ? (
        <LineageGroup title={t('ancestors')} people={data.ancestors} />
      ) : null}
      {data.descendants.length > 0 ? (
        <LineageGroup title={t('descendants')} people={data.descendants} />
      ) : null}
    </section>
  );
}

function LineageGroup({
  title,
  people,
}: {
  title: string;
  people: Array<{ uuid: string; title: string; relationship: string }>;
}) {
  return (
    <div>
      <h3 className="mb-2 text-sm font-medium text-[var(--color-ink-secondary)]">{title}</h3>
      <ul className="flex flex-col gap-1">
        {people.map((person) => (
          <li key={person.uuid} className="text-sm">
            <Link
              href={entityPublicHref('person' as EntityType, person.uuid)}
              className="font-medium text-[var(--color-scholar)] hover:underline"
            >
              {person.title}
            </Link>
            <span className="ms-2 text-[var(--color-ink-tertiary)]">({person.relationship})</span>
          </li>
        ))}
      </ul>
    </div>
  );
}
