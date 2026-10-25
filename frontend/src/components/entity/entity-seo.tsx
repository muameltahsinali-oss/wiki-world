import type { Metadata } from 'next';
import { routing } from '@/i18n/routing';
import type { PublicEntity } from '@/lib/entity/types';
import { ENTITY_TYPE_ROUTES } from '@/lib/entity/types';

const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL ?? 'http://localhost:3000';

function absoluteUrl(pathOrUrl: string): string {
  if (pathOrUrl.startsWith('http://') || pathOrUrl.startsWith('https://')) {
    return pathOrUrl;
  }
  const path = pathOrUrl.startsWith('/') ? pathOrUrl : `/${pathOrUrl}`;
  return `${SITE_URL}${path}`;
}

export function buildEntityCanonical(entity: PublicEntity, locale: string): string {
  const typeRoute = ENTITY_TYPE_ROUTES[entity.type];
  if (entity.seo.canonical_url) {
    return absoluteUrl(entity.seo.canonical_url);
  }
  return absoluteUrl(`/${locale}/${typeRoute}/${entity.uuid}`);
}

export function buildEntityHreflang(entity: PublicEntity): Record<string, string> {
  const typeRoute = ENTITY_TYPE_ROUTES[entity.type];
  const languages: Record<string, string> = {};

  for (const altLocale of routing.locales) {
    languages[altLocale] = absoluteUrl(`/${altLocale}/${typeRoute}/${entity.uuid}`);
  }
  languages['x-default'] = absoluteUrl(`/${routing.defaultLocale}/${typeRoute}/${entity.uuid}`);

  return languages;
}

export function buildEntityMetadata(entity: PublicEntity, locale: string): Metadata {
  const title = entity.seo.meta_title?.trim() || entity.title;
  const description =
    entity.seo.meta_description?.trim() || entity.summary?.trim() || undefined;
  const canonical = buildEntityCanonical(entity, locale);
  const languages = buildEntityHreflang(entity);
  const typeRoute = ENTITY_TYPE_ROUTES[entity.type];

  return {
    title,
    description,
    alternates: {
      canonical,
      languages,
    },
    openGraph: {
      title,
      description,
      type: 'article',
      locale,
      url: canonical,
      siteName: 'Wiki World',
      publishedTime: entity.published_at ?? undefined,
    },
    twitter: {
      card: 'summary',
      title,
      description,
    },
    other: {
      'content:entity_type': entity.type,
      'content:entity_path': `/${typeRoute}/${entity.uuid}`,
    },
  };
}

export function buildEntityJsonLd(entity: PublicEntity, locale: string): Record<string, unknown> {
  const url = buildEntityCanonical(entity, locale);
  const schemaType = schemaTypeForEntity(entity.type);

  const base: Record<string, unknown> = {
    '@context': 'https://schema.org',
    '@type': schemaType,
    name: entity.title,
    headline: entity.title,
    description: entity.summary ?? undefined,
    url,
    mainEntityOfPage: url,
    inLanguage: locale,
    datePublished: entity.published_at ?? undefined,
    isAccessibleForFree: true,
  };

  if (entity.type === 'person') {
    return {
      ...base,
      '@type': 'Person',
      birthDate: (entity.profile?.birth_date_hijri as string | undefined) ?? undefined,
      deathDate: (entity.profile?.death_date_hijri as string | undefined) ?? undefined,
      alternateName: (entity.profile?.kunya as string | undefined) ?? undefined,
    };
  }

  if (entity.type === 'event') {
    return {
      ...base,
      '@type': 'Event',
      startDate: (entity.profile?.start_date_hijri as string | undefined) ?? undefined,
      endDate: (entity.profile?.end_date_hijri as string | undefined) ?? undefined,
      location: (entity.profile?.location as string | undefined) ?? undefined,
    };
  }

  if (entity.type === 'place') {
    return {
      ...base,
      '@type': 'Place',
      latitude: entity.profile?.latitude ?? undefined,
      longitude: entity.profile?.longitude ?? undefined,
      address: {
        '@type': 'PostalAddress',
        addressCountry: (entity.profile?.country as string | undefined) ?? undefined,
        addressRegion: (entity.profile?.region as string | undefined) ?? undefined,
      },
    };
  }

  if (entity.type === 'book') {
    return {
      ...base,
      '@type': 'Book',
      isbn: (entity.profile?.isbn as string | undefined) ?? undefined,
      numberOfPages: entity.profile?.page_count ?? undefined,
      author: entity.profile?.authors ?? undefined,
    };
  }

  if (entity.type === 'source') {
    return {
      ...base,
      '@type': 'CreativeWork',
      genre: (entity.profile?.source_type as string | undefined) ?? undefined,
    };
  }

  return { ...base, '@type': 'Article' };
}

function schemaTypeForEntity(type: PublicEntity['type']): string {
  switch (type) {
    case 'person':
      return 'Person';
    case 'event':
      return 'Event';
    case 'place':
      return 'Place';
    case 'book':
      return 'Book';
    case 'source':
      return 'CreativeWork';
    default:
      return 'Article';
  }
}

/** Server-safe JSON-LD script for entity pages. */
export function EntityJsonLd({ entity, locale }: { entity: PublicEntity; locale: string }) {
  const jsonLd = buildEntityJsonLd(entity, locale);

  return (
    <script
      type="application/ld+json"
      dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
    />
  );
}

/**
 * Alias for pages that need structured data alongside generateMetadata.
 * Prefer `buildEntityMetadata` for title/description/canonical/hreflang.
 */
export function EntitySeoHead(props: { entity: PublicEntity; locale: string }) {
  return <EntityJsonLd {...props} />;
}
