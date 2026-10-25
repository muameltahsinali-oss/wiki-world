'use client';

import { GripVertical, Plus, Trash2 } from 'lucide-react';
import { useTranslations } from 'next-intl';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { CitationBlockEditor } from '@/components/cms/block-editor/citation-block-editor';
import { EntityLinkBlockEditor } from '@/components/cms/block-editor/entity-link-block-editor';
import { ImageBlockEditor } from '@/components/cms/block-editor/image-block-editor';
import { blockText } from '@/lib/cms/blocks';
import type { BlockType, ContentBlock } from '@/lib/cms/types';

interface BlockEditorProps {
  blocks: ContentBlock[];
  onChange: (blocks: ContentBlock[]) => void;
  locale: string;
}

const BASIC_TYPES: BlockType[] = ['paragraph', 'heading', 'quote', 'list'];

export function BlockEditor({ blocks, onChange, locale }: BlockEditorProps) {
  const t = useTranslations('cms.blockEditor');

  function updateBlock(index: number, next: ContentBlock) {
    const copy = [...blocks];
    copy[index] = next;
    onChange(copy);
  }

  function removeBlock(index: number) {
    onChange(blocks.filter((_, i) => i !== index));
  }

  function addBlock(type: BlockType) {
    const id = `block-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
    const block: ContentBlock = { id, type, content: { text: '' }, heading_level: type === 'heading' ? 2 : undefined };
    if (type === 'list') block.content = { items: [''] };
    onChange([...blocks, block]);
  }

  return (
    <div className="flex flex-col gap-4">
      {blocks.map((block, index) => (
        <div
          key={block.id}
          className="group rounded-lg border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] p-4"
        >
          <div className="mb-3 flex items-center justify-between gap-2">
            <div className="flex items-center gap-2 text-xs text-[var(--color-ink-tertiary)]">
              <GripVertical className="h-4 w-4" aria-hidden />
              {t(`types.${block.type}`)}
            </div>
            <Button type="button" variant="ghost" size="sm" onClick={() => removeBlock(index)} aria-label={t('remove')}>
              <Trash2 className="h-4 w-4" />
            </Button>
          </div>

          {block.type === 'heading' ? (
            <div className="flex flex-col gap-3">
              <label className="flex flex-col gap-1 text-sm">
                <span>{t('headingLevel')}</span>
                <select
                  className="h-10 rounded-md border border-[var(--color-border-default)] px-3"
                  value={block.heading_level ?? 2}
                  onChange={(e) =>
                    updateBlock(index, { ...block, heading_level: Number(e.target.value) })
                  }
                >
                  {[1, 2, 3, 4, 5, 6].map((level) => (
                    <option key={level} value={level}>
                      H{level}
                    </option>
                  ))}
                </select>
              </label>
              <Input
                label={t('headingText')}
                value={blockText(block.content)}
                onChange={(e) => updateBlock(index, { ...block, content: { text: e.target.value } })}
              />
            </div>
          ) : null}

          {block.type === 'paragraph' || block.type === 'quote' ? (
            <label className="flex flex-col gap-1 text-sm">
              <span>{t('content')}</span>
              <textarea
                className="min-h-24 rounded-md border border-[var(--color-border-default)] bg-[var(--color-surface-base)] p-3"
                value={blockText(block.content)}
                onChange={(e) => updateBlock(index, { ...block, content: { text: e.target.value } })}
              />
            </label>
          ) : null}

          {block.type === 'list' ? (
            <div className="flex flex-col gap-2">
              {(block.content.items ?? ['']).map((item, itemIndex) => (
                <div key={itemIndex} className="flex gap-2">
                  <Input
                    value={item}
                    onChange={(e) => {
                      const items = [...(block.content.items ?? [''])];
                      items[itemIndex] = e.target.value;
                      updateBlock(index, { ...block, content: { items } });
                    }}
                  />
                  <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    onClick={() => {
                      const items = (block.content.items ?? []).filter((_, i) => i !== itemIndex);
                      updateBlock(index, { ...block, content: { items: items.length ? items : [''] } });
                    }}
                  >
                    <Trash2 className="h-4 w-4" />
                  </Button>
                </div>
              ))}
              <Button
                type="button"
                variant="secondary"
                size="sm"
                onClick={() =>
                  updateBlock(index, {
                    ...block,
                    content: { items: [...(block.content.items ?? []), ''] },
                  })
                }
              >
                {t('addListItem')}
              </Button>
            </div>
          ) : null}

          {block.type === 'citation' ? (
            <CitationBlockEditor
              block={block}
              locale={locale}
              onChange={(next) => updateBlock(index, next)}
            />
          ) : null}

          {block.type === 'entity_link' ? (
            <EntityLinkBlockEditor
              block={block}
              locale={locale}
              onChange={(next) => updateBlock(index, next)}
            />
          ) : null}

          {block.type === 'image' ? (
            <ImageBlockEditor block={block} locale={locale} onChange={(next) => updateBlock(index, next)} />
          ) : null}

          {block.type === 'divider' ? (
            <hr className="border-[var(--color-border-strong)]" />
          ) : null}
        </div>
      ))}

      <div className="flex flex-wrap gap-2">
        {BASIC_TYPES.map((type) => (
          <Button key={type} type="button" variant="secondary" size="sm" onClick={() => addBlock(type)}>
            <Plus className="h-4 w-4" />
            {t(`types.${type}`)}
          </Button>
        ))}
        <Button type="button" variant="secondary" size="sm" onClick={() => addBlock('citation')}>
          <Plus className="h-4 w-4" />
          {t('types.citation')}
        </Button>
        <Button type="button" variant="secondary" size="sm" onClick={() => addBlock('entity_link')}>
          <Plus className="h-4 w-4" />
          {t('types.entity_link')}
        </Button>
        <Button type="button" variant="secondary" size="sm" onClick={() => addBlock('image')}>
          <Plus className="h-4 w-4" />
          {t('types.image')}
        </Button>
      </div>
    </div>
  );
}
