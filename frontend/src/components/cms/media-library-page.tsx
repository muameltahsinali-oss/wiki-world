'use client';

import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Upload } from 'lucide-react';
import { useLocale, useTranslations } from 'next-intl';
import * as React from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Skeleton } from '@/components/ui/skeleton';
import { getEditorialMedia, uploadEditorialMedia } from '@/lib/api/editorial';

export function MediaLibraryPage() {
  const t = useTranslations('cms.mediaPage');
  const locale = useLocale();
  const queryClient = useQueryClient();
  const fileInputRef = React.useRef<HTMLInputElement>(null);
  const [typeFilter, setTypeFilter] = React.useState('');
  const [statusFilter, setStatusFilter] = React.useState('');

  const mediaQuery = useQuery({
    queryKey: ['editorial-media', locale, typeFilter, statusFilter],
    queryFn: () =>
      getEditorialMedia(locale, {
        type: typeFilter || undefined,
        status: statusFilter || undefined,
        page: 1,
      }),
  });

  const uploadMutation = useMutation({
    mutationFn: (file: File) => uploadEditorialMedia(file, undefined, locale),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['editorial-media'] }),
  });

  const items = mediaQuery.data?.data ?? [];

  return (
    <div className="flex flex-col gap-6">
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold text-[var(--color-ink)]">{t('title')}</h1>
          <p className="mt-1 text-[var(--color-ink-secondary)]">{t('subtitle')}</p>
        </div>
        <div className="flex gap-2">
          <input
            ref={fileInputRef}
            type="file"
            className="hidden"
            onChange={(e) => {
              const file = e.target.files?.[0];
              if (file) uploadMutation.mutate(file);
              e.target.value = '';
            }}
          />
          <Button type="button" onClick={() => fileInputRef.current?.click()} loading={uploadMutation.isPending}>
            <Upload className="h-4 w-4" />
            {t('upload')}
          </Button>
        </div>
      </div>

      <div className="flex flex-wrap gap-3">
        <label className="flex flex-col gap-1 text-sm">
          <span>{t('filterType')}</span>
          <select
            className="h-10 rounded-md border border-[var(--color-border-default)] px-3"
            value={typeFilter}
            onChange={(e) => setTypeFilter(e.target.value)}
          >
            <option value="">{t('all')}</option>
            <option value="image">{t('images')}</option>
            <option value="file">{t('files')}</option>
          </select>
        </label>
        <label className="flex flex-col gap-1 text-sm">
          <span>{t('filterStatus')}</span>
          <select
            className="h-10 rounded-md border border-[var(--color-border-default)] px-3"
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
          >
            <option value="">{t('all')}</option>
            <option value="ready">{t('ready')}</option>
            <option value="processing">{t('processing')}</option>
          </select>
        </label>
      </div>

      {mediaQuery.isLoading ? (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {Array.from({ length: 6 }).map((_, i) => (
            <Skeleton key={i} className="h-36 rounded-xl" />
          ))}
        </div>
      ) : items.length === 0 ? (
        <EmptyState title={t('emptyTitle')} description={t('emptyDescription')} />
      ) : (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {items.map((item) => (
            <div
              key={item.uuid}
              className="rounded-xl border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] p-4"
            >
              <div className="mb-2 flex items-center justify-between gap-2">
                <Badge variant={item.media_type === 'image' ? 'info' : 'default'}>{item.media_type}</Badge>
                <Badge variant={item.status === 'ready' ? 'success' : 'warning'}>{item.status}</Badge>
              </div>
              <p className="truncate text-sm font-medium text-[var(--color-ink)]">{item.original_filename}</p>
              <p className="mt-1 text-xs text-[var(--color-ink-tertiary)]">{item.mime_type}</p>
              {item.alt_text ? (
                <p className="mt-2 text-xs text-[var(--color-ink-secondary)]">{item.alt_text}</p>
              ) : null}
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
