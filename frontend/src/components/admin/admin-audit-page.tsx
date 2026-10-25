'use client';

import { useQuery } from '@tanstack/react-query';
import { useTranslations } from 'next-intl';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import { getAdminAuditLogs } from '@/lib/api/admin';

export function AdminAuditPage() {
  const t = useTranslations('admin');
  const [page, setPage] = useState(1);
  const [action, setAction] = useState('');

  const auditQuery = useQuery({
    queryKey: ['admin-audit', page, action],
    queryFn: () => getAdminAuditLogs(page, action || undefined),
  });

  const logs = auditQuery.data?.data ?? [];
  const pagination = auditQuery.data?.meta?.pagination;

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-semibold text-[var(--color-ink)]">{t('auditTitle')}</h1>
        <p className="mt-1 text-[var(--color-ink-secondary)]">{t('auditSubtitle')}</p>
      </div>

      <Input
        label={t('filterAction')}
        value={action}
        onChange={(e) => {
          setAction(e.target.value);
          setPage(1);
        }}
        placeholder={t('filterActionPlaceholder')}
      />

      {auditQuery.isLoading ? (
        <Skeleton className="h-64" />
      ) : logs.length === 0 ? (
        <EmptyState title={t('noAuditLogs')} description={t('noAuditLogsHint')} />
      ) : (
        <div className="overflow-x-auto rounded-lg border border-[var(--color-border-default)]">
          <table className="min-w-full text-sm">
            <thead className="bg-[var(--color-surface-sunken)] text-start">
              <tr>
                <th className="px-4 py-3 font-medium">{t('action')}</th>
                <th className="px-4 py-3 font-medium">{t('actor')}</th>
                <th className="px-4 py-3 font-medium">{t('createdAt')}</th>
              </tr>
            </thead>
            <tbody>
              {logs.map((log) => (
                <tr key={log.id} className="border-t border-[var(--color-border-default)]">
                  <td className="px-4 py-3">
                    <div className="font-medium">{log.action}</div>
                    <div className="text-[var(--color-ink-secondary)]">
                      {log.subject_type}
                      {log.subject_id ? ` #${log.subject_id}` : ''}
                    </div>
                  </td>
                  <td className="px-4 py-3">{log.actor?.email ?? '—'}</td>
                  <td className="px-4 py-3">{new Date(log.created_at).toLocaleString()}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {pagination && pagination.last_page > 1 ? (
        <div className="flex items-center gap-2">
          <Button variant="secondary" disabled={page <= 1} onClick={() => setPage((p) => p - 1)}>
            {t('prevPage')}
          </Button>
          <span className="text-sm text-[var(--color-ink-secondary)]">
            {page} / {pagination.last_page}
          </span>
          <Button
            variant="secondary"
            disabled={page >= pagination.last_page}
            onClick={() => setPage((p) => p + 1)}
          >
            {t('nextPage')}
          </Button>
        </div>
      ) : null}
    </div>
  );
}
