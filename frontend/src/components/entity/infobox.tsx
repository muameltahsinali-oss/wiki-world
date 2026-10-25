'use client';

import { useTranslations } from 'next-intl';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { EntityType } from '@/components/ui/badge';
import { isSacredPersonProfile } from '@/lib/entity/person-label';
import { profileString } from '@/lib/entity/utils';
import { cn } from '@/lib/utils';

export interface InfoboxProps {
  entityType: EntityType;
  profile: Record<string, unknown> | null;
  className?: string;
}

interface FieldConfig {
  key: string;
  labelKey: string;
}

const PROFILE_FIELDS: Record<EntityType, FieldConfig[]> = {
  person: [
    { key: 'kunya', labelKey: 'kunya' },
    { key: 'birth_date_hijri', labelKey: 'birthHijri' },
    { key: 'death_date_hijri', labelKey: 'deathHijri' },
    { key: 'birth_place', labelKey: 'birthPlace' },
    { key: 'death_place', labelKey: 'deathPlace' },
    { key: 'titles', labelKey: 'titles' },
    { key: 'is_imam', labelKey: 'isImam' },
    { key: 'imam_number', labelKey: 'imamNumber' },
  ],
  event: [
    { key: 'start_date_hijri', labelKey: 'startDate' },
    { key: 'end_date_hijri', labelKey: 'endDate' },
    { key: 'era', labelKey: 'era' },
    { key: 'location', labelKey: 'location' },
    { key: 'significance', labelKey: 'significance' },
  ],
  place: [
    { key: 'place_type', labelKey: 'placeType' },
    { key: 'latitude', labelKey: 'latitude' },
    { key: 'longitude', labelKey: 'longitude' },
    { key: 'country', labelKey: 'country' },
    { key: 'region', labelKey: 'region' },
  ],
  concept: [
    { key: 'arabic_term', labelKey: 'arabicTerm' },
    { key: 'concept_category', labelKey: 'category' },
    { key: 'transliteration', labelKey: 'transliteration' },
  ],
  source: [
    { key: 'source_type', labelKey: 'sourceType' },
    { key: 'reliability_tier', labelKey: 'reliabilityTier' },
    { key: 'authors', labelKey: 'authors' },
    { key: 'publication_year', labelKey: 'publicationYear' },
    { key: 'publisher', labelKey: 'publisher' },
  ],
  book: [
    { key: 'authors', labelKey: 'authors' },
    { key: 'publication_year', labelKey: 'publicationYear' },
    { key: 'publisher', labelKey: 'publisher' },
    { key: 'isbn', labelKey: 'isbn' },
    { key: 'chapter_count', labelKey: 'chapterCount' },
  ],
  article: [],
  verse: [
    { key: 'surah', labelKey: 'surah' },
    { key: 'ayah', labelKey: 'ayah' },
    { key: 'juz', labelKey: 'juz' },
    { key: 'page', labelKey: 'page' },
  ],
};

function resolvePersonFieldLabelKey(key: string, sacred: boolean): string {
  if (!sacred) {
    return key;
  }

  if (key === 'deathHijri') {
    return 'deathHijriSacred';
  }
  if (key === 'deathPlace') {
    return 'deathPlaceSacred';
  }

  return key;
}

function formatFieldValue(key: string, value: string): string {
  if (key === 'is_imam' || key === 'isImam') {
    return value === 'true' || value === '1' ? '✓' : '—';
  }
  return value;
}

export function Infobox({ entityType, profile, className }: InfoboxProps) {
  const t = useTranslations('entity.infobox');
  const fields = PROFILE_FIELDS[entityType];
  const sacredPerson = entityType === 'person' && isSacredPersonProfile(profile);
  const rows = fields
    .map((field) => {
      const raw = profileString(profile, field.key);
      if (!raw) return null;
      const labelKey = resolvePersonFieldLabelKey(field.labelKey, sacredPerson);
      return { label: t(labelKey), value: formatFieldValue(field.key, raw) };
    })
    .filter((row): row is { label: string; value: string } => row !== null);

  const reliabilityTier = profileString(profile, 'reliability_tier');

  if (rows.length === 0 && !reliabilityTier) return null;

  return (
    <Card className={cn('overflow-hidden', className)}>
      <CardHeader className="border-b border-[var(--color-border-default)] bg-[var(--color-surface-sunken)] py-3">
        <CardTitle className="text-sm font-semibold uppercase tracking-wide text-[var(--color-ink-secondary)]">
          {t(sacredPerson ? 'titleSacred' : 'title')}
        </CardTitle>
      </CardHeader>
      <CardContent className="p-0">
        {reliabilityTier && entityType === 'source' ? (
          <div className="border-b border-[var(--color-border-default)] px-4 py-3">
            <Badge variant="info">{t('reliabilityTier')}: {reliabilityTier}</Badge>
          </div>
        ) : null}
        {rows.length > 0 ? (
          <dl className="divide-y divide-[var(--color-border-default)]">
            {rows.map((row) => (
              <div key={row.label} className="grid grid-cols-[1fr_1.2fr] gap-2 px-4 py-2.5 text-sm">
                <dt className="text-[var(--color-ink-tertiary)]">{row.label}</dt>
                <dd className="font-medium text-[var(--color-ink)]">{row.value}</dd>
              </div>
            ))}
          </dl>
        ) : null}
        {entityType === 'place' && profileString(profile, 'latitude') && profileString(profile, 'longitude') ? (
          <div className="border-t border-[var(--color-border-default)] px-4 py-3">
            <a
              href={`https://www.openstreetmap.org/?mlat=${profileString(profile, 'latitude')}&mlon=${profileString(profile, 'longitude')}#map=10/${profileString(profile, 'latitude')}/${profileString(profile, 'longitude')}`}
              target="_blank"
              rel="noopener noreferrer"
              className="text-sm text-[var(--color-scholar)] hover:underline"
            >
              {t('viewOnMap')}
            </a>
          </div>
        ) : null}
      </CardContent>
    </Card>
  );
}
