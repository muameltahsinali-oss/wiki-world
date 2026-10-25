import React from 'react';
import { Link } from '@/i18n/navigation';
import { entityPublicHref } from '@/lib/entity/types';
import type { EntityType } from '@/components/ui/badge';

interface Linkable {
  title: string;
  uuid: string;
  type: string;
}

export function autoLinkText(
  text: string,
  entities: Linkable[],
  currentUuid?: string
): React.ReactNode[] {
  if (!text) return [text];
  if (!entities || entities.length === 0) return [text];

  // Filter out the current entity to avoid self-linking
  // Only match titles with length > 2
  const candidates = entities
    .filter((e) => e.uuid !== currentUuid && e.title && e.title.trim().length > 2)
    .sort((a, b) => b.title.length - a.title.length);

  if (candidates.length === 0) return [text];

  // Escape special regex characters in titles
  const escapedTitles = candidates.map((c) =>
    c.title.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&')
  );

  // Match absolute phrases
  const regex = new RegExp(`(${escapedTitles.join('|')})`, 'g');

  const parts = text.split(regex);
  if (parts.length <= 1) return [text];

  const candidateMap = new Map<string, Linkable>();
  for (const c of candidates) {
    candidateMap.set(c.title, c);
  }

  return parts.map((part, index) => {
    // Odd indices are matched titles
    if (index % 2 === 1) {
      const match = candidateMap.get(part);
      if (match) {
        return (
          <Link
            key={index}
            href={entityPublicHref(match.type as EntityType, match.uuid)}
            className="text-[var(--color-scholar)] underline hover:text-[var(--color-scholar-hover)] transition-colors font-medium"
          >
            {part}
          </Link>
        );
      }
    }
    return part;
  });
}
