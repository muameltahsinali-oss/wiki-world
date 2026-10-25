'use client';

import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useTranslations } from 'next-intl';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import {
  assignAdminUserRole,
  getAdminUsers,
  removeAdminUserRole,
} from '@/lib/api/admin';

const ROLES = ['registered', 'editor', 'reviewer', 'senior_editor', 'admin'] as const;

export function AdminUsersPage() {
  const t = useTranslations('admin');
  const queryClient = useQueryClient();
  const [page, setPage] = useState(1);
  const [query, setQuery] = useState('');

  const usersQuery = useQuery({
    queryKey: ['admin-users', page, query],
    queryFn: () => getAdminUsers(page, query || undefined),
  });

  const assignMutation = useMutation({
    mutationFn: ({ uuid, role }: { uuid: string; role: string }) => assignAdminUserRole(uuid, role),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin-users'] }),
  });

  const removeMutation = useMutation({
    mutationFn: ({ uuid, role }: { uuid: string; role: string }) => removeAdminUserRole(uuid, role),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin-users'] }),
  });

  const users = usersQuery.data?.data ?? [];
  const pagination = usersQuery.data?.meta?.pagination;

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-semibold text-[var(--color-ink)]">{t('usersTitle')}</h1>
        <p className="mt-1 text-[var(--color-ink-secondary)]">{t('usersSubtitle')}</p>
      </div>

      <Input
        label={t('searchUsers')}
        value={query}
        onChange={(e) => {
          setQuery(e.target.value);
          setPage(1);
        }}
        placeholder={t('searchUsersPlaceholder')}
      />

      {usersQuery.isLoading ? (
        <Skeleton className="h-64" />
      ) : users.length === 0 ? (
        <EmptyState title={t('noUsers')} description={t('noUsersHint')} />
      ) : (
        <div className="overflow-x-auto rounded-lg border border-[var(--color-border-default)]">
          <table className="min-w-full text-sm">
            <thead className="bg-[var(--color-surface-sunken)] text-start">
              <tr>
                <th className="px-4 py-3 font-medium">{t('email')}</th>
                <th className="px-4 py-3 font-medium">{t('roles')}</th>
                <th className="px-4 py-3 font-medium">{t('actions')}</th>
              </tr>
            </thead>
            <tbody>
              {users.map((user) => (
                <tr key={user.uuid} className="border-t border-[var(--color-border-default)]">
                  <td className="px-4 py-3">
                    <div className="font-medium">{user.display_name ?? user.email}</div>
                    <div className="text-[var(--color-ink-secondary)]">{user.email}</div>
                  </td>
                  <td className="px-4 py-3">
                    <div className="flex flex-wrap gap-1">
                      {user.roles.map((role) => (
                        <Badge key={role}>
                          {role}
                        </Badge>
                      ))}
                    </div>
                  </td>
                  <td className="px-4 py-3">
                    <div className="flex flex-wrap gap-2">
                      {ROLES.map((role) => {
                        const hasRole = user.roles.includes(role);
                        return (
                          <Button
                            key={role}
                            size="sm"
                            variant={hasRole ? 'secondary' : 'primary'}
                            disabled={assignMutation.isPending || removeMutation.isPending}
                            onClick={() =>
                              hasRole
                                ? removeMutation.mutate({ uuid: user.uuid, role })
                                : assignMutation.mutate({ uuid: user.uuid, role })
                            }
                          >
                            {hasRole ? t('removeRole', { role }) : t('addRole', { role })}
                          </Button>
                        );
                      })}
                    </div>
                  </td>
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
