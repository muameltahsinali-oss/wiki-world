'use client';

import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useLocale, useTranslations } from 'next-intl';
import * as React from 'react';
import { BlockEditor } from '@/components/cms/block-editor/block-editor';
import { SeoPanel, VersionHistoryPanel } from '@/components/cms/entity-side-panels';
import { PreviewPanel } from '@/components/cms/preview-panel';
import { PublishChecklistDrawer } from '@/components/cms/publish-checklist-drawer';
import { ReferenceEditorPanel } from '@/components/cms/reference-editor-panel';
import { RelationshipEditorPanel } from '@/components/cms/relationship-editor-panel';
import { WorkflowBadge } from '@/components/cms/workflow-badge';
import { Button } from '@/components/ui/button';
import { Dialog } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import { Tabs } from '@/components/ui/tabs';
import { Link } from '@/i18n/navigation';
import {
  getEditorialEntity,
  publishEntity,
  saveEntityDraft,
  submitEntity,
  unpublishEntity,
} from '@/lib/api/editorial';
import { normalizeApiBlock, serializeBlock } from '@/lib/cms/blocks';
import type { ContentBlock, SaveStatus } from '@/lib/cms/types';
import { useCmsKeyboardShortcuts } from '@/hooks/use-cms-keyboard-shortcuts';

export function EntityEditorPage({ uuid }: { uuid: string }) {
  const t = useTranslations('cms');
  const locale = useLocale();
  const queryClient = useQueryClient();

  const [title, setTitle] = React.useState('');
  const [slug, setSlug] = React.useState('');
  const [summary, setSummary] = React.useState('');
  const [blocks, setBlocks] = React.useState<ContentBlock[]>([]);
  const [saveStatus, setSaveStatus] = React.useState<SaveStatus>('idle');
  const [checklistOpen, setChecklistOpen] = React.useState(false);
  const [commandOpen, setCommandOpen] = React.useState(false);
  const saveTimer = React.useRef<ReturnType<typeof setTimeout> | null>(null);
  const dirtyRef = React.useRef(false);

  const entityQuery = useQuery({
    queryKey: ['editorial-entity', uuid, locale],
    queryFn: () => getEditorialEntity(uuid, locale),
  });

  const entity = entityQuery.data?.data;

  React.useEffect(() => {
    if (!entity) return;
    setTitle(entity.title ?? '');
    setSlug(entity.slug ?? '');
    setSummary(entity.summary ?? '');
    setBlocks(entity.blocks.map((block, index) => normalizeApiBlock(block, index)));
    dirtyRef.current = false;
    setSaveStatus('idle');
  }, [entity]);

  const saveMutation = useMutation({
    mutationFn: () =>
      saveEntityDraft(
        uuid,
        {
          locale,
          title,
          slug,
          summary,
          blocks: blocks.map((block, index) => serializeBlock(block, index)),
        },
        locale,
      ),
    onMutate: () => setSaveStatus('saving'),
    onSuccess: (response) => {
      queryClient.setQueryData(['editorial-entity', uuid, locale], response);
      dirtyRef.current = false;
      setSaveStatus('saved');
    },
    onError: () => setSaveStatus('error'),
  });

  const submitMutation = useMutation({
    mutationFn: () => submitEntity(uuid, locale),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['editorial-entity', uuid] }),
  });

  const publishMutation = useMutation({
    mutationFn: () => publishEntity(uuid, locale),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['editorial-entity', uuid] }),
  });

  const unpublishMutation = useMutation({
    mutationFn: () => unpublishEntity(uuid, locale),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['editorial-entity', uuid] }),
  });

  const scheduleSave = React.useCallback(() => {
    dirtyRef.current = true;
    setSaveStatus('idle');
    if (saveTimer.current) clearTimeout(saveTimer.current);
    saveTimer.current = setTimeout(() => {
      saveMutation.mutate();
    }, 2000);
  }, [saveMutation]);

  React.useEffect(() => {
    return () => {
      if (saveTimer.current) clearTimeout(saveTimer.current);
    };
  }, []);

  function markDirty<T>(setter: (value: T) => void, value: T) {
    setter(value);
    scheduleSave();
  }

  const handleSaveNow = React.useCallback(() => {
    if (saveTimer.current) clearTimeout(saveTimer.current);
    saveMutation.mutate();
  }, [saveMutation]);

  useCmsKeyboardShortcuts({
    onSave: handleSaveNow,
    onSubmit: () => submitMutation.mutate(),
    onCommandPalette: () => setCommandOpen(true),
  });

  if (entityQuery.isLoading) {
    return <Skeleton className="h-96 w-full rounded-xl" />;
  }

  if (!entity) {
    return <p className="text-sm text-[var(--color-ink-secondary)]">{t('entityNotFound')}</p>;
  }

  const panelTabs = [
    { id: 'preview', label: t('panels.preview'), content: <PreviewPanel uuid={uuid} locale={locale} /> },
    {
      id: 'seo',
      label: t('panels.seo'),
      content: (
        <SeoPanel
          uuid={uuid}
          locale={locale}
          onSaved={() => queryClient.invalidateQueries({ queryKey: ['editorial-entity', uuid] })}
        />
      ),
    },
    {
      id: 'relationships',
      label: t('panels.relationships'),
      content: <RelationshipEditorPanel uuid={uuid} locale={locale} />,
    },
    {
      id: 'references',
      label: t('panels.references'),
      content: <ReferenceEditorPanel uuid={uuid} locale={locale} />,
    },
    {
      id: 'versions',
      label: t('panels.versions'),
      content: (
        <VersionHistoryPanel
          uuid={uuid}
          locale={locale}
          onRestored={() => queryClient.invalidateQueries({ queryKey: ['editorial-entity', uuid] })}
        />
      ),
    },
  ];

  return (
    <div className="flex min-h-full flex-col gap-4 xl:flex-row">
      <div className="min-w-0 flex-1">
        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
          <div className="flex items-center gap-3">
            <Link href="/cms/entities" className="text-sm text-[var(--color-scholar)] hover:underline">
              {t('backToEntities')}
            </Link>
            <WorkflowBadge state={entity.workflow_state} />
            <SaveStatusIndicator status={saveStatus} />
          </div>
          <div className="flex flex-wrap gap-2">
            <Button type="button" variant="secondary" size="sm" onClick={handleSaveNow} loading={saveMutation.isPending}>
              {t('saveNow')}
            </Button>
            <Button
              type="button"
              variant="secondary"
              size="sm"
              onClick={() => submitMutation.mutate()}
              loading={submitMutation.isPending}
            >
              {t('submitReview')}
            </Button>
            <Button type="button" variant="secondary" size="sm" onClick={() => setChecklistOpen(true)}>
              {t('publishChecklistLabel')}
            </Button>
            <Button
              type="button"
              size="sm"
              onClick={() => publishMutation.mutate()}
              loading={publishMutation.isPending}
            >
              {t('publish')}
            </Button>
            {entity.workflow_state === 'published' ? (
              <Button
                type="button"
                variant="ghost"
                size="sm"
                onClick={() => unpublishMutation.mutate()}
                loading={unpublishMutation.isPending}
              >
                {t('unpublish')}
              </Button>
            ) : null}
          </div>
        </div>

        <div className="flex flex-col gap-4">
          <Input
            label={t('fieldTitle')}
            value={title}
            onChange={(e) => markDirty(setTitle, e.target.value)}
          />
          <Input label={t('fieldSlug')} value={slug} onChange={(e) => markDirty(setSlug, e.target.value)} />
          <label className="flex flex-col gap-1.5 text-sm">
            <span className="font-medium text-[var(--color-ink)]">{t('fieldSummary')}</span>
            <textarea
              className="min-h-24 rounded-md border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] p-3"
              value={summary}
              onChange={(e) => markDirty(setSummary, e.target.value)}
            />
          </label>
          <div>
            <h2 className="mb-3 text-sm font-semibold text-[var(--color-ink)]">{t('blocksTitle')}</h2>
            <BlockEditor
              blocks={blocks}
              locale={locale}
              onChange={(next) => {
                setBlocks(next);
                scheduleSave();
              }}
            />
          </div>
        </div>
      </div>

      <aside className="w-full shrink-0 xl:w-80">
        <Tabs items={panelTabs} defaultTab="preview" />
      </aside>

      <PublishChecklistDrawer
        uuid={uuid}
        locale={locale}
        open={checklistOpen}
        onOpenChange={setChecklistOpen}
      />

      <Dialog open={commandOpen} onOpenChange={setCommandOpen} title={t('commandPalette')}>
        <ul className="flex flex-col gap-2 text-sm">
          <li>
            <button type="button" className="w-full rounded-md px-3 py-2 text-start hover:bg-[var(--color-surface-sunken)]" onClick={() => { handleSaveNow(); setCommandOpen(false); }}>
              {t('shortcutSave')}
            </button>
          </li>
          <li>
            <button type="button" className="w-full rounded-md px-3 py-2 text-start hover:bg-[var(--color-surface-sunken)]" onClick={() => { submitMutation.mutate(); setCommandOpen(false); }}>
              {t('shortcutSubmit')}
            </button>
          </li>
          <li>
            <button type="button" className="w-full rounded-md px-3 py-2 text-start hover:bg-[var(--color-surface-sunken)]" onClick={() => { setChecklistOpen(true); setCommandOpen(false); }}>
              {t('publishChecklistLabel')}
            </button>
          </li>
        </ul>
      </Dialog>
    </div>
  );
}

function SaveStatusIndicator({ status }: { status: SaveStatus }) {
  const t = useTranslations('cms.saveStatus');
  if (status === 'idle') return null;
  return (
    <span className="text-xs text-[var(--color-ink-tertiary)]" aria-live="polite">
      {t(status)}
    </span>
  );
}
