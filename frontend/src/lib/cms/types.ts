import type { EntityType } from '@/components/ui/badge';

export type WorkflowState =
  | 'draft'
  | 'submitted'
  | 'in_review'
  | 'approved'
  | 'published'
  | 'unpublished'
  | 'archived';

export type BlockType =
  | 'paragraph'
  | 'heading'
  | 'quote'
  | 'list'
  | 'citation'
  | 'entity_link'
  | 'image'
  | 'divider';

export interface BlockContent {
  text?: string;
  items?: string[];
  source_uuid?: string;
  source_title?: string;
  entity_uuid?: string;
  entity_title?: string;
  media_uuid?: string;
  alt_text?: string;
  reference?: string;
}

export interface ContentBlock {
  id: string;
  type: BlockType;
  content: BlockContent;
  heading_level?: number;
  sort_order?: number;
}

export interface EditorialEntityListItem {
  uuid: string;
  entity_type: EntityType;
  domain: string;
  workflow_state: WorkflowState;
  locale: string;
  title: string;
  slug: string;
  summary: string | null;
  relationships_count: number;
  updated_at: string | null;
}

export interface EditorialEntity extends EditorialEntityListItem {
  translation_status: string | null;
  is_indexable: boolean;
  published_at: string | null;
  version_number: number | null;
  profile: Record<string, unknown> | null;
  blocks: Array<{
    type: BlockType;
    content: BlockContent | string;
    heading_level?: number;
    sort_order?: number;
  }>;
  meta_title: string | null;
  meta_description: string | null;
  created_at: string | null;
}

export interface DashboardStats {
  drafts_count: number;
  in_review_count: number;
  approved_count: number;
  published_this_week: number;
}

export interface EditorialSubmission {
  id: number;
  status: string;
  submitted_at: string;
  entity_uuid: string;
  workflow_state: WorkflowState;
  submitted_by_uuid: string;
}

export interface PublishCheck {
  code: string;
  passed: boolean;
  message?: string;
  required?: number;
  actual?: number;
}

export interface PublishReadiness {
  ready: boolean;
  checks: PublishCheck[];
}

export interface EntityRelationship {
  id: number;
  relationship_type: string;
  direction: 'outgoing' | 'incoming';
  metadata: Record<string, unknown>;
  peer_entity: {
    uuid: string;
    entity_type: EntityType;
    workflow_state: WorkflowState;
  };
  created_at: string | null;
}

export interface EntityReference {
  id: number;
  source_uuid: string;
  page_reference: string | null;
  quote_excerpt: string | null;
  created_at: string;
}

export interface EntityVersion {
  id: number;
  version_number: number;
  change_summary: string | null;
  created_by_user_id: number | null;
  created_at: string;
}

export interface MediaItem {
  uuid: string;
  media_type: 'image' | 'file';
  original_filename: string;
  alt_text: string | null;
  mime_type: string;
  status: string;
  file_size_bytes: number;
  created_at: string;
}

export interface EntitySeo {
  meta_title: string | null;
  meta_description: string | null;
  canonical_url: string | null;
  is_indexable: boolean;
}

export type SaveStatus = 'idle' | 'saving' | 'saved' | 'error';
