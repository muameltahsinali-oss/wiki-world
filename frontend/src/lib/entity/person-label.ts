import { useCallback } from 'react';
import { useTranslations } from 'next-intl';
import type { EntityType } from '@/components/ui/badge';

export type PersonLabelKey =
  | 'masumImam'
  | 'masumLady'
  | 'prophet'
  | 'masumFigure'
  | 'ahlulbaytFigure'
  | 'figure';

export interface PersonLabelHints {
  is_masum?: boolean;
  imam_order?: number | null;
  generation?: number | null;
}

export interface EntityLabelOptions {
  hints?: PersonLabelHints | null;
  title?: string;
  module?: string | null;
}

function titleLooksLikeImam(title?: string): boolean {
  if (!title) return false;
  return /الإمام|امام/i.test(title);
}

function titleLooksLikeProphet(title?: string): boolean {
  if (!title) return false;
  return /النبي|نبي\s|رسول/i.test(title);
}

function titleLooksLikeLady(title?: string): boolean {
  if (!title) return false;
  return /السيدة|سيدة/i.test(title);
}

export function resolvePersonLabelKey(
  hints?: PersonLabelHints | null,
  title?: string,
  module?: string | null,
): PersonLabelKey {
  const imamOrder = hints?.imam_order;
  const isMasum = Boolean(hints?.is_masum);
  const generation = hints?.generation;

  if (imamOrder != null && imamOrder > 0) {
    return 'masumImam';
  }

  if (isMasum) {
    if (generation === 0 || titleLooksLikeProphet(title)) {
      return 'prophet';
    }
    if (titleLooksLikeLady(title)) {
      return 'masumLady';
    }
    if (titleLooksLikeImam(title)) {
      return 'masumImam';
    }
    return 'masumFigure';
  }

  if (module === 'ahlulbayt') {
    return 'ahlulbaytFigure';
  }

  if (titleLooksLikeImam(title)) {
    return 'masumImam';
  }
  if (titleLooksLikeProphet(title)) {
    return 'prophet';
  }
  if (titleLooksLikeLady(title)) {
    return 'masumLady';
  }

  return 'figure';
}

export function getEntityTypeLabel(
  translatePersonLabel: (key: PersonLabelKey) => string,
  translateEntityType: (type: EntityType) => string,
  entityType: EntityType,
  options?: EntityLabelOptions,
): string {
  if (entityType !== 'person') {
    return translateEntityType(entityType);
  }

  const key = resolvePersonLabelKey(options?.hints, options?.title, options?.module);
  return translatePersonLabel(key);
}

export function useEntityTypeLabel() {
  const personLabels = useTranslations('personLabels');
  const entityTypes = useTranslations('entityTypes');

  return useCallback(
    (entityType: EntityType, options?: EntityLabelOptions) =>
      getEntityTypeLabel(
        (key) => personLabels(key),
        (type) => entityTypes(type),
        entityType,
        options,
      ),
    [entityTypes, personLabels],
  );
}

export function isSacredPersonProfile(profile: Record<string, unknown> | null | undefined): boolean {
  if (!profile) return false;
  if (profile.is_masum === true || profile.is_masum === 1 || profile.is_masum === '1') {
    return true;
  }
  const imamOrder = profile.imam_order;
  return typeof imamOrder === 'number' && imamOrder > 0;
}
