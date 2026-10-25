'use client';

import { useQuery } from '@tanstack/react-query';
import { useTranslations } from 'next-intl';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { getAdminAnalyticsOverview } from '@/lib/api/admin';

export function AdminOverviewPage() {
  const t = useTranslations('admin');
  const overviewQuery = useQuery({
    queryKey: ['admin-analytics-overview'],
    queryFn: () => getAdminAnalyticsOverview(),
  });

  const data = overviewQuery.data?.data;

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-semibold text-[var(--color-ink)]">{t('overviewTitle')}</h1>
        <p className="mt-1 text-[var(--color-ink-secondary)]">{t('overviewSubtitle')}</p>
      </div>

      {overviewQuery.isLoading ? (
        <div className="grid gap-4 sm:grid-cols-3">
          <Skeleton className="h-28" />
          <Skeleton className="h-28" />
          <Skeleton className="h-28" />
        </div>
      ) : (
        <div className="grid gap-4 sm:grid-cols-3">
          <Card>
            <CardHeader>
              <CardDescription>{t('totalViews')}</CardDescription>
              <CardTitle>{data?.total_page_views ?? 0}</CardTitle>
            </CardHeader>
          </Card>
          <Card>
            <CardHeader>
              <CardDescription>{t('uniqueSessions')}</CardDescription>
              <CardTitle>{data?.unique_sessions ?? 0}</CardTitle>
            </CardHeader>
          </Card>
          <Card>
            <CardHeader>
              <CardDescription>{t('periodDays')}</CardDescription>
              <CardTitle>{data?.period_days ?? 30}</CardTitle>
            </CardHeader>
          </Card>
        </div>
      )}

      {data?.top_entities?.length ? (
        <Card>
          <CardHeader>
            <CardDescription>{t('topEntities')}</CardDescription>
            <CardTitle className="text-base font-normal">
              <ul className="mt-2 flex flex-col gap-1 text-sm">
                {data.top_entities.map((entity) => (
                  <li key={entity.uuid} className="flex justify-between gap-4">
                    <span className="truncate font-mono text-xs">{entity.uuid}</span>
                    <span>{entity.view_count}</span>
                  </li>
                ))}
              </ul>
            </CardTitle>
          </CardHeader>
        </Card>
      ) : null}
    </div>
  );
}
