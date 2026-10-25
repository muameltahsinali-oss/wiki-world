'use client';

import { useQuery } from '@tanstack/react-query';
import { useLocale, useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';
import { StatCard } from '@/components/cms/stat-card';
import { WorkflowBadge } from '@/components/cms/workflow-badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { EmptyState } from '@/components/ui/empty-state';
import { Skeleton } from '@/components/ui/skeleton';
import {
  getEditorialDashboard,
  getEditorialDrafts,
  getEditorialSubmissions,
} from '@/lib/api/editorial';

export function CmsDashboard() {
  const t = useTranslations('cms');
  const locale = useLocale();

  const statsQuery = useQuery({
    queryKey: ['editorial-dashboard', locale],
    queryFn: () => getEditorialDashboard(locale),
  });

  const draftsQuery = useQuery({
    queryKey: ['editorial-drafts', locale],
    queryFn: () => getEditorialDrafts(locale, 1),
  });

  const submissionsQuery = useQuery({
    queryKey: ['editorial-submissions', locale],
    queryFn: () => getEditorialSubmissions(locale),
  });

  const stats = statsQuery.data?.data;
  const drafts = draftsQuery.data?.data ?? [];
  const submissions = submissionsQuery.data?.data ?? [];

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-semibold text-[var(--color-ink)]">{t('dashboardTitle')}</h1>
        <p className="mt-1 text-[var(--color-ink-secondary)]">{t('dashboardSubtitle')}</p>
      </div>

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {statsQuery.isLoading ? (
          Array.from({ length: 4 }).map((_, i) => <Skeleton key={i} className="h-28 rounded-xl" />)
        ) : (
          <>
            <StatCard title={t('stats.drafts')} value={stats?.drafts_count ?? 0} />
            <StatCard title={t('stats.inReview')} value={stats?.in_review_count ?? 0} />
            <StatCard title={t('stats.approved')} value={stats?.approved_count ?? 0} />
            <StatCard title={t('stats.publishedWeek')} value={stats?.published_this_week ?? 0} />
          </>
        )}
      </div>

      <div className="grid gap-4 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>{t('recentDrafts')}</CardTitle>
            <CardDescription>{t('recentDraftsHint')}</CardDescription>
          </CardHeader>
          <CardContent className="flex flex-col gap-3">
            {draftsQuery.isLoading ? (
              <Skeleton className="h-24 rounded-lg" />
            ) : drafts.length === 0 ? (
              <EmptyState title={t('noDrafts')} description={t('noDraftsHint')} />
            ) : (
              drafts.slice(0, 5).map((item) => (
                <Link
                  key={item.uuid}
                  href={`/cms/entities/${item.uuid}`}
                  className="flex items-center justify-between rounded-lg border border-[var(--color-border-default)] p-3 transition-colors hover:bg-[var(--color-surface-sunken)]"
                >
                  <div className="min-w-0">
                    <p className="truncate font-medium text-[var(--color-ink)]">{item.title}</p>
                    <p className="text-xs text-[var(--color-ink-tertiary)]">{item.slug}</p>
                  </div>
                  <WorkflowBadge state={item.workflow_state} />
                </Link>
              ))
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>{t('reviewQueue')}</CardTitle>
            <CardDescription>{t('reviewQueueHint')}</CardDescription>
          </CardHeader>
          <CardContent className="flex flex-col gap-3">
            {submissionsQuery.isLoading ? (
              <Skeleton className="h-24 rounded-lg" />
            ) : submissions.length === 0 ? (
              <EmptyState title={t('noSubmissions')} description={t('noSubmissionsHint')} />
            ) : (
              submissions.slice(0, 5).map((item) => (
                <Link
                  key={item.id}
                  href="/cms/reviews"
                  className="flex items-center justify-between rounded-lg border border-[var(--color-border-default)] p-3 transition-colors hover:bg-[var(--color-surface-sunken)]"
                >
                  <div className="min-w-0">
                    <p className="truncate font-medium text-[var(--color-ink)]">{item.entity_uuid}</p>
                    <p className="text-xs text-[var(--color-ink-tertiary)]">
                      {new Date(item.submitted_at).toLocaleString(locale)}
                    </p>
                  </div>
                  <WorkflowBadge state={item.workflow_state} />
                </Link>
              ))
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
