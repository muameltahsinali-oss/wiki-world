'use client';

import { useEffect } from 'react';
import { useLocale, useTranslations } from 'next-intl';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Bookmark, BookmarkCheck } from 'lucide-react';
import { ContentBlockRenderer } from '@/components/entity/content-block-renderer';
import { EntityPageLayout } from '@/components/entity/entity-page-layout';
import { Infobox } from '@/components/entity/infobox';
import { RelatedEntitiesPanel } from '@/components/entity/related-entities-panel';
import { LineagePanel } from '@/components/ahlulbayt/lineage-panel';
import { Alert } from '@/components/ui/alert';
import { Badge, entityBadgeVariant, type EntityType } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { useRouter } from '@/i18n/navigation';
import { ApiClientError } from '@/lib/api/client';
import {
  addBookmark,
  getPublicEntityByType,
  getPublicRelationships,
  getBookmarks,
  recordReadingHistory,
  removeBookmark,
  getPublicEntities,
} from '@/lib/api/public';
import { getStoredToken } from '@/lib/auth/token';
import {
  estimateReadingTimeMinutes,
  extractTocHeadings,
  normalizePublicBlocks,
} from '@/lib/entity/utils';
import { useEntityTypeLabel } from '@/lib/entity/person-label';

export interface EntityPublicPageProps {
  entityType: EntityType;
  uuid: string;
  showToc?: boolean;
  showReadingProgress?: boolean;
  showLineage?: boolean;
}

export function EntityPublicPage({
  entityType,
  uuid,
  showToc = false,
  showReadingProgress = false,
  showLineage = false,
}: EntityPublicPageProps) {
  const locale = useLocale();
  const router = useRouter();
  const t = useTranslations('entity');
  const entityTypeLabel = useEntityTypeLabel();
  const queryClient = useQueryClient();
  const isAuthenticated = Boolean(getStoredToken());

  const entityQuery = useQuery({
    queryKey: ['public-entity', entityType, uuid, locale],
    queryFn: () => getPublicEntityByType(locale, entityType, uuid),
    retry: false,
  });

  const relationshipsQuery = useQuery({
    queryKey: ['public-relationships', uuid, locale],
    queryFn: () => getPublicRelationships(locale, uuid),
    enabled: Boolean(entityQuery.data),
  });

  const bookmarksQuery = useQuery({
    queryKey: ['bookmarks', locale],
    queryFn: () => getBookmarks(locale),
    enabled: isAuthenticated,
  });

  const placesQuery = useQuery({
    queryKey: ['linkable-places', locale],
    queryFn: () => getPublicEntities(locale, 'places', { per_page: 100 }),
  });

  const personsQuery = useQuery({
    queryKey: ['linkable-persons', locale],
    queryFn: () => getPublicEntities(locale, 'persons', { per_page: 100 }),
  });

  const linkableEntities = [
    ...(placesQuery.data?.data ?? []).map((p) => ({ title: p.title, uuid: p.uuid, type: 'place' })),
    ...(personsQuery.data?.data ?? []).map((p) => ({ title: p.title, uuid: p.uuid, type: 'person' })),
  ];

  const bookmarkMutation = useMutation({
    mutationFn: async (bookmarked: boolean) => {
      if (bookmarked) {
        await removeBookmark(uuid, locale);
      } else {
        await addBookmark(uuid, locale);
      }
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bookmarks', locale] });
    },
  });

  useEffect(() => {
    if (!entityQuery.data || !isAuthenticated) return;
    recordReadingHistory(uuid, locale).catch(() => undefined);
  }, [entityQuery.data, isAuthenticated, uuid, locale]);

  if (entityQuery.isLoading) {
    return (
      <main className="mx-auto max-w-7xl px-4 py-12">
        <Skeleton className="mb-4 h-10 w-2/3" />
        <Skeleton className="mb-8 h-6 w-full max-w-xl" />
        <Skeleton className="h-64 w-full" />
      </main>
    );
  }

  if (entityQuery.error instanceof ApiClientError && entityQuery.error.status === 404) {
    return (
      <main className="mx-auto max-w-7xl px-4 py-12">
        <Alert variant="error">{t('notFound')}</Alert>
      </main>
    );
  }

  if (entityQuery.error || !entityQuery.data) {
    return (
      <main className="mx-auto max-w-7xl px-4 py-12">
        <Alert variant="error">{t('loadError')}</Alert>
        <Button className="mt-4" variant="secondary" onClick={() => entityQuery.refetch()}>
          {t('retry')}
        </Button>
      </main>
    );
  }

  const entity = entityQuery.data.data;
  const blocks = normalizePublicBlocks(entity.blocks);
  const toc = showToc ? extractTocHeadings(blocks) : [];
  const readingMinutes = estimateReadingTimeMinutes(blocks);
  const isBookmarked = bookmarksQuery.data?.data.some((b) => b.entity_uuid === uuid) ?? false;

  const handleBookmark = () => {
    if (!isAuthenticated) {
      router.push('/login');
      return;
    }
    bookmarkMutation.mutate(isBookmarked);
  };

  const personHints =
    entityType === 'person' && entity.profile
      ? {
          is_masum: Boolean(entity.profile.is_masum),
          imam_order:
            typeof entity.profile.imam_order === 'number' ? entity.profile.imam_order : null,
          generation:
            typeof entity.profile.ahlulbayt_generation === 'number'
              ? entity.profile.ahlulbayt_generation
              : null,
        }
      : undefined;

  return (
    <EntityPageLayout
      title={entity.title}
      summary={entity.summary}
      showReadingProgress={showReadingProgress}
      toc={toc}
      infobox={<Infobox entityType={entityType} profile={entity.profile} />}
      meta={
        <>
          <Badge variant={entityBadgeVariant(entityType)}>
            {entityTypeLabel(entityType, {
              hints: personHints,
              title: entity.title,
            })}
          </Badge>
          {entity.published_at ? (
            <span className="text-[var(--color-ink-tertiary)]">
              {t('published', { date: new Date(entity.published_at).toLocaleDateString(locale) })}
            </span>
          ) : null}
          {showToc || showReadingProgress ? (
            <span className="text-[var(--color-ink-tertiary)]">
              {t('readingTime', { minutes: readingMinutes })}
            </span>
          ) : null}
        </>
      }
      actions={
        <Button
          type="button"
          variant="secondary"
          size="sm"
          loading={bookmarkMutation.isPending}
          onClick={handleBookmark}
          aria-pressed={isBookmarked}
        >
          {isBookmarked ? (
            <BookmarkCheck className="h-4 w-4" aria-hidden />
          ) : (
            <Bookmark className="h-4 w-4" aria-hidden />
          )}
          {isBookmarked ? t('bookmarked') : t('bookmark')}
        </Button>
      }
      footer={
        <>
          {showLineage && entityType === 'person' ? <LineagePanel uuid={uuid} /> : null}
          {relationshipsQuery.data ? (
            <RelatedEntitiesPanel relationships={relationshipsQuery.data.data} />
          ) : null}
        </>
      }
    >
      <ContentBlockRenderer blocks={blocks} linkableEntities={linkableEntities} currentUuid={uuid} />
    </EntityPageLayout>
  );
}
