'use client';

import { createElement } from 'react';
import { useTranslations } from 'next-intl';
import { CitationPopover } from '@/components/entity/citation-popover';
import { CitationBlock } from '@/components/ui/citation-block';
import { Link } from '@/i18n/navigation';
import { blockText } from '@/lib/cms/blocks';
import type { ContentBlock } from '@/lib/cms/types';
import { entityPublicHref } from '@/lib/entity/types';
import type { EntityType } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

import { autoLinkText } from '@/lib/entity/cross-link';

export interface ContentBlockRendererProps {
  blocks: ContentBlock[];
  className?: string;
  linkableEntities?: Array<{ title: string; uuid: string; type: string }>;
  currentUuid?: string;
}

function headingTag(level: number): 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6' {
  const clamped = Math.min(Math.max(level, 1), 6);
  return `h${clamped}` as 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6';
}

export function ContentBlockRenderer({
  blocks,
  className,
  linkableEntities = [],
  currentUuid,
}: ContentBlockRendererProps) {
  const t = useTranslations('entity.blocks');

  return (
    <div className={cn('flex flex-col gap-6', className)}>
      {blocks.map((block) => {
        switch (block.type) {
          case 'paragraph':
            return (
              <p key={block.id} className="text-base leading-relaxed text-[var(--color-ink)]">
                {autoLinkText(blockText(block.content), linkableEntities, currentUuid)}
              </p>
            );

          case 'heading': {
            const tag = headingTag(block.heading_level ?? 2);
            const sizeClass =
              block.heading_level === 1
                ? 'text-2xl'
                : block.heading_level === 2
                  ? 'text-xl'
                  : 'text-lg';
            return createElement(
              tag,
              {
                key: block.id,
                id: block.id,
                className: cn('scroll-mt-24 font-semibold text-[var(--color-ink)]', sizeClass),
              },
              blockText(block.content),
            );
          }

          case 'quote':
            return (
              <blockquote
                key={block.id}
                className="border-s-4 border-[var(--color-scholar)] ps-4 text-lg italic text-[var(--color-ink-secondary)]"
              >
                {autoLinkText(blockText(block.content), linkableEntities, currentUuid)}
              </blockquote>
            );

          case 'list':
            return (
              <ul key={block.id} className="list-disc space-y-2 ps-6 text-[var(--color-ink)]">
                {(block.content.items ?? []).map((item, index) => (
                  <li key={index}>{item}</li>
                ))}
              </ul>
            );

          case 'citation':
            return (
              <CitationPopover
                key={block.id}
                sourceUuid={block.content.source_uuid}
                sourceTitle={block.content.source_title ?? t('unknownSource')}
                reference={block.content.reference}
              >
                <CitationBlock
                  sourceTitle={block.content.source_title ?? t('unknownSource')}
                  reference={block.content.reference}
                  href={
                    block.content.source_uuid
                      ? entityPublicHref('source', block.content.source_uuid)
                      : undefined
                  }
                />
              </CitationPopover>
            );

          case 'entity_link': {
            const entityType = (block.content as { entity_type?: EntityType }).entity_type ?? 'article';
            const uuid = block.content.entity_uuid;
            const title = block.content.entity_title ?? t('linkedEntity');
            if (!uuid) {
              return (
                <p key={block.id} className="text-sm text-[var(--color-ink-secondary)]">
                  {title}
                </p>
              );
            }
            return (
              <Link
                key={block.id}
                href={entityPublicHref(entityType, uuid)}
                className="inline-flex items-center gap-2 rounded-lg border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] px-4 py-3 text-sm font-medium text-[var(--color-scholar)] transition-colors hover:border-[var(--color-scholar)]"
              >
                {title}
              </Link>
            );
          }

          case 'image':
            return (
              <figure key={block.id} className="overflow-hidden rounded-lg border border-[var(--color-border-default)]">
                <div className="flex aspect-video items-center justify-center bg-[var(--color-surface-sunken)] text-sm text-[var(--color-ink-tertiary)]">
                  {block.content.alt_text ?? t('imagePlaceholder')}
                </div>
                {block.content.alt_text ? (
                  <figcaption className="px-3 py-2 text-xs text-[var(--color-ink-tertiary)]">
                    {block.content.alt_text}
                  </figcaption>
                ) : null}
              </figure>
            );

          case 'divider':
            return <hr key={block.id} className="border-[var(--color-border-default)]" />;

          default:
            return null;
        }
      })}
    </div>
  );
}
