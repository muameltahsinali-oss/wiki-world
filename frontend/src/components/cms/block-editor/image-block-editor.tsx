'use client';

import { useQuery } from '@tanstack/react-query';
import { useTranslations } from 'next-intl';
import { Input } from '@/components/ui/input';
import { getEditorialMedia } from '@/lib/api/editorial';
import type { ContentBlock } from '@/lib/cms/types';

export function ImageBlockEditor({
  block,
  locale,
  onChange,
}: {
  block: ContentBlock;
  locale: string;
  onChange: (block: ContentBlock) => void;
}) {
  const t = useTranslations('cms.blockEditor');

  const mediaQuery = useQuery({
    queryKey: ['editorial-media-picker', locale],
    queryFn: () => getEditorialMedia(locale, { type: 'image', page: 1 }),
  });

  const images = (mediaQuery.data?.data ?? []).filter((item) => item.media_type === 'image');

  return (
    <div className="flex flex-col gap-3">
      <label className="flex flex-col gap-1 text-sm">
        <span>{t('selectImage')}</span>
        <select
          className="h-10 rounded-md border border-[var(--color-border-default)] px-3"
          value={block.content.media_uuid ?? ''}
          onChange={(e) => {
            const selected = images.find((img) => img.uuid === e.target.value);
            onChange({
              ...block,
              content: {
                ...block.content,
                media_uuid: e.target.value,
                alt_text: selected?.alt_text ?? block.content.alt_text ?? '',
              },
            });
          }}
        >
          <option value="">{t('chooseImage')}</option>
          {images.map((image) => (
            <option key={image.uuid} value={image.uuid}>
              {image.original_filename}
            </option>
          ))}
        </select>
      </label>
      <Input
        label={t('altText')}
        value={block.content.alt_text ?? ''}
        onChange={(e) =>
          onChange({ ...block, content: { ...block.content, alt_text: e.target.value } })
        }
      />
    </div>
  );
}
