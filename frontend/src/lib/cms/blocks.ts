import type { BlockContent, BlockType, ContentBlock } from '@/lib/cms/types';

export function blockText(content: BlockContent | string | undefined): string {
  if (typeof content === 'string') return content;
  return content?.text ?? '';
}

export function normalizeApiBlock(
  block: {
    type: BlockType;
    content: BlockContent | string;
    heading_level?: number;
    sort_order?: number;
  },
  index: number,
): ContentBlock {
  const content =
    typeof block.content === 'string'
      ? { text: block.content }
      : (block.content ?? {});

  return {
    id: `block-${index}-${block.type}`,
    type: block.type,
    content,
    heading_level: block.heading_level,
    sort_order: block.sort_order ?? index,
  };
}

export function serializeBlock(block: ContentBlock, index: number) {
  const base = {
    type: block.type,
    sort_order: index,
    heading_level: block.heading_level,
  };

  switch (block.type) {
    case 'paragraph':
    case 'quote':
      return { ...base, content: blockText(block.content) };
    case 'heading':
      return {
        ...base,
        content: { text: blockText(block.content) },
        heading_level: block.heading_level ?? 2,
      };
    case 'list':
      return { ...base, content: { items: block.content.items ?? [] } };
    case 'citation':
      return {
        ...base,
        content: {
          source_uuid: block.content.source_uuid,
          source_title: block.content.source_title,
          reference: block.content.reference,
        },
      };
    case 'entity_link':
      return {
        ...base,
        content: {
          entity_uuid: block.content.entity_uuid,
          entity_title: block.content.entity_title,
        },
      };
    case 'image':
      return {
        ...base,
        content: {
          media_uuid: block.content.media_uuid,
          alt_text: block.content.alt_text,
        },
      };
    case 'divider':
      return { ...base, content: {} };
    default:
      return { ...base, content: block.content };
  }
}

export function createEmptyBlock(type: BlockType): ContentBlock {
  const id = `block-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;

  switch (type) {
    case 'heading':
      return { id, type, content: { text: '' }, heading_level: 2 };
    case 'list':
      return { id, type, content: { items: [''] } };
    case 'citation':
      return { id, type, content: { source_uuid: '', source_title: '' } };
    case 'entity_link':
      return { id, type, content: { entity_uuid: '', entity_title: '' } };
    case 'image':
      return { id, type, content: { media_uuid: '', alt_text: '' } };
    case 'divider':
      return { id, type, content: {} };
    default:
      return { id, type, content: { text: '' } };
  }
}
