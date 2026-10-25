'use client';

import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useTranslations } from 'next-intl';
import { Button } from '@/components/ui/button';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { getAdminSettings, patchAdminSettings, patchFeatureToggles } from '@/lib/api/admin';

export function AdminSettingsPage() {
  const t = useTranslations('admin');
  const queryClient = useQueryClient();

  const settingsQuery = useQuery({
    queryKey: ['admin-settings'],
    queryFn: () => getAdminSettings(),
  });

  const settingsMutation = useMutation({
    mutationFn: patchAdminSettings,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin-settings'] }),
  });

  const togglesMutation = useMutation({
    mutationFn: patchFeatureToggles,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin-settings'] }),
  });

  const settings = settingsQuery.data?.data;

  if (settingsQuery.isLoading) {
    return <Skeleton className="h-64" />;
  }

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-semibold text-[var(--color-ink)]">{t('settingsTitle')}</h1>
        <p className="mt-1 text-[var(--color-ink-secondary)]">{t('settingsSubtitle')}</p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>{t('maintenanceMode')}</CardTitle>
          <CardDescription>{t('maintenanceModeHint')}</CardDescription>
          <Button
            className="mt-4 w-fit"
            variant={settings?.maintenance_mode ? 'danger' : 'primary'}
            disabled={settingsMutation.isPending}
            onClick={() =>
              settingsMutation.mutate({ maintenance_mode: !settings?.maintenance_mode })
            }
          >
            {settings?.maintenance_mode ? t('disableMaintenance') : t('enableMaintenance')}
          </Button>
        </CardHeader>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{t('featureToggles')}</CardTitle>
          <CardDescription>{t('featureTogglesHint')}</CardDescription>
          <div className="mt-4 flex flex-col gap-2">
            {(settings?.feature_toggles ?? []).map((toggle) => (
              <div key={toggle.key} className="flex items-center justify-between gap-4">
                <span className="text-sm">{toggle.key}</span>
                <Button
                  size="sm"
                  variant={toggle.is_enabled ? 'primary' : 'secondary'}
                  disabled={togglesMutation.isPending}
                  onClick={() =>
                    togglesMutation.mutate({ [toggle.key]: !toggle.is_enabled })
                  }
                >
                  {toggle.is_enabled ? t('enabled') : t('disabled')}
                </Button>
              </div>
            ))}
          </div>
        </CardHeader>
      </Card>
    </div>
  );
}
