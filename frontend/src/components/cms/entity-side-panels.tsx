'use client';

import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useTranslations } from 'next-intl';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { getEntitySeo, getEntityVersions, patchEntitySeo, restoreEntityVersion } from '@/lib/api/editorial';
import * as React from 'react';

export function SeoPanel({
  uuid,
  locale,
  onSaved,
}: {
  uuid: string;
  locale: string;
  onSaved?: () => void;
}) {
  const t = useTranslations('cms.seo');
  const queryClient = useQueryClient();

  const seoQuery = useQuery({
    queryKey: ['entity-seo', uuid, locale],
    queryFn: () => getEntitySeo(uuid, locale),
  });

  const [metaTitle, setMetaTitle] = React.useState('');
  const [metaDescription, setMetaDescription] = React.useState('');

  React.useEffect(() => {
    if (seoQuery.data?.data) {
      setMetaTitle(seoQuery.data.data.meta_title ?? '');
      setMetaDescription(seoQuery.data.data.meta_description ?? '');
    }
  }, [seoQuery.data]);

  const saveMutation = useMutation({
    mutationFn: () =>
      patchEntitySeo(uuid, { locale, meta_title: metaTitle, meta_description: metaDescription }, locale),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['entity-seo', uuid] });
      onSaved?.();
    },
  });

  return (
    <div className="flex flex-col gap-3">
      <h3 className="text-sm font-semibold text-[var(--color-ink)]">{t('title')}</h3>
      <Input label={t('metaTitle')} value={metaTitle} onChange={(e) => setMetaTitle(e.target.value)} />
      <label className="flex flex-col gap-1 text-sm">
        <span>{t('metaDescription')}</span>
        <textarea
          className="min-h-24 rounded-md border border-[var(--color-border-default)] p-3"
          value={metaDescription}
          onChange={(e) => setMetaDescription(e.target.value)}
        />
      </label>
      <Button type="button" size="sm" loading={saveMutation.isPending} onClick={() => saveMutation.mutate()}>
        {t('save')}
      </Button>
    </div>
  );
}

export function VersionHistoryPanel({
  uuid,
  locale,
  onRestored,
}: {
  uuid: string;
  locale: string;
  onRestored?: () => void;
}) {
  const t = useTranslations('cms.versions');
  const queryClient = useQueryClient();

  const versionsQuery = useQuery({
    queryKey: ['entity-versions', uuid, locale],
    queryFn: () => getEntityVersions(uuid, locale),
  });

  const restoreMutation = useMutation({
    mutationFn: (version: number) => restoreEntityVersion(uuid, version, locale),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['entity-versions', uuid] });
      queryClient.invalidateQueries({ queryKey: ['editorial-entity', uuid] });
      onRestored?.();
    },
  });

  const versions = versionsQuery.data?.data ?? [];

  return (
    <div className="flex flex-col gap-3">
      <h3 className="text-sm font-semibold text-[var(--color-ink)]">{t('title')}</h3>
      <ul className="flex flex-col gap-2">
        {versions.map((version) => (
          <li
            key={version.id}
            className="flex items-center justify-between gap-2 rounded-lg border border-[var(--color-border-default)] p-3 text-sm"
          >
            <div>
              <p className="font-medium">
                {t('version')} {version.version_number}
              </p>
              <p className="text-xs text-[var(--color-ink-tertiary)]">
                {new Date(version.created_at).toLocaleString(locale)}
              </p>
            </div>
            <Button
              type="button"
              variant="secondary"
              size="sm"
              onClick={() => restoreMutation.mutate(version.version_number)}
            >
              {t('restore')}
            </Button>
          </li>
        ))}
      </ul>
    </div>
  );
}
