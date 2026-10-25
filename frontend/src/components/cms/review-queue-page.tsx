'use client';

import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useLocale, useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';
import { WorkflowBadge } from '@/components/cms/workflow-badge';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Skeleton } from '@/components/ui/skeleton';
import { getEditorialSubmissions, reviewSubmission } from '@/lib/api/editorial';

export function ReviewQueuePage() {
  const t = useTranslations('cms.reviewsPage');
  const locale = useLocale();
  const queryClient = useQueryClient();

  const submissionsQuery = useQuery({
    queryKey: ['editorial-submissions', locale],
    queryFn: () => getEditorialSubmissions(locale),
  });

  const reviewMutation = useMutation({
    mutationFn: ({ id, decision }: { id: number; decision: 'approve' | 'reject' }) =>
      reviewSubmission(id, decision, undefined, locale),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['editorial-submissions'] }),
  });

  const submissions = submissionsQuery.data?.data ?? [];

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-semibold text-[var(--color-ink)]">{t('title')}</h1>
        <p className="mt-1 text-[var(--color-ink-secondary)]">{t('subtitle')}</p>
      </div>

      {submissionsQuery.isLoading ? (
        <Skeleton className="h-32 w-full rounded-xl" />
      ) : submissions.length === 0 ? (
        <EmptyState title={t('emptyTitle')} description={t('emptyDescription')} />
      ) : (
        <div className="flex flex-col gap-3">
          {submissions.map((item) => (
            <div
              key={item.id}
              className="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-[var(--color-border-default)] p-4"
            >
              <div className="min-w-0">
                <Link
                  href={`/cms/entities/${item.entity_uuid}`}
                  className="font-medium text-[var(--color-scholar)] hover:underline"
                >
                  {item.entity_uuid}
                </Link>
                <p className="text-xs text-[var(--color-ink-tertiary)]">
                  {new Date(item.submitted_at).toLocaleString(locale)}
                </p>
                <WorkflowBadge state={item.workflow_state} />
              </div>
              <div className="flex gap-2">
                <Button
                  type="button"
                  size="sm"
                  onClick={() => reviewMutation.mutate({ id: item.id, decision: 'approve' })}
                  loading={reviewMutation.isPending}
                >
                  {t('approve')}
                </Button>
                <Button
                  type="button"
                  variant="danger"
                  size="sm"
                  onClick={() => reviewMutation.mutate({ id: item.id, decision: 'reject' })}
                  loading={reviewMutation.isPending}
                >
                  {t('reject')}
                </Button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
