import type { EntityType } from '@/components/ui/badge';
import type { BlockContent, BlockType } from '@/lib/cms/types';

export interface PublicEntitySeo {
  meta_title: string | null;
  meta_description: string | null;
  canonical_url?: string | null;
}

export interface PublicEntityBlock {
  type: BlockType;
  content: BlockContent | string;
  heading_level?: number;
  sort_order?: number;
}

export interface PublicEntity {
  uuid: string;
  type: EntityType;
  title: string;
  slug: string;
  summary: string | null;
  locale: string;
  direction: 'rtl' | 'ltr';
  workflow_state: string;
  published_at: string | null;
  profile: Record<string, unknown> | null;
  blocks: PublicEntityBlock[];
  relationships_count: number;
  seo: PublicEntitySeo;
}

export interface PersonLabelHints {
  is_masum?: boolean;
  imam_order?: number | null;
  generation?: number | null;
}

export interface PublicEntityListItem {
  uuid: string;
  type: EntityType;
  title: string;
  slug: string;
  summary: string | null;
  published_at?: string | null;
  module?: string | null;
  person_hints?: PersonLabelHints | null;
}

export interface PublicRelationship {
  id: number;
  relationship_type: string;
  direction: 'outgoing' | 'incoming';
  metadata: Record<string, unknown>;
  peer_entity: {
    uuid: string;
    entity_type: EntityType;
    workflow_state: string;
    title?: string;
  };
  created_at?: string | null;
}

export interface Bookmark {
  entity_uuid: string;
  created_at: string;
}

export interface ReadingHistoryEntry {
  entity_uuid: string;
  viewed_at: string;
}

export const ENTITY_TYPE_ROUTES: Record<EntityType, string> = {
  person: 'persons',
  article: 'articles',
  event: 'events',
  place: 'places',
  concept: 'concepts',
  source: 'sources',
  book: 'books',
  verse: 'verses',
};

export function entityPublicHref(type: EntityType, uuid: string, slug?: string | null): string {
  if (type === 'verse' && slug) {
    const reference = slug.includes(':') ? slug : slug.replace('-', ':');
    if (/^\d+:\d+$/.test(reference)) {
      return `/quran/verses/${reference}`;
    }
  }

  return `/${ENTITY_TYPE_ROUTES[type]}/${uuid}`;
}
