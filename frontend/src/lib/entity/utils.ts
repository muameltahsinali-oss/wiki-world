import { blockText, normalizeApiBlock } from '@/lib/cms/blocks';
import type { ContentBlock } from '@/lib/cms/types';
import type { PublicEntityBlock } from '@/lib/entity/types';

export function normalizePublicBlocks(blocks: PublicEntityBlock[]): ContentBlock[] {
  return blocks.map((block, index) => normalizeApiBlock(block, index));
}

export function extractTocHeadings(blocks: ContentBlock[]): Array<{ id: string; text: string; level: number }> {
  return blocks
    .filter((block) => block.type === 'heading')
    .map((block) => ({
      id: block.id,
      text: blockText(block.content),
      level: block.heading_level ?? 2,
    }))
    .filter((heading) => heading.text.trim().length > 0);
}

export function estimateReadingTimeMinutes(blocks: ContentBlock[]): number {
  const text = blocks.map((block) => blockText(block.content)).join(' ');
  const words = text.trim().split(/\s+/).filter(Boolean).length;
  return Math.max(1, Math.ceil(words / 200));
}

export function profileString(profile: Record<string, unknown> | null, key: string): string | null {
  if (!profile) return null;
  const value = profile[key];
  if (value === null || value === undefined || value === '') return null;
  return String(value);
}
