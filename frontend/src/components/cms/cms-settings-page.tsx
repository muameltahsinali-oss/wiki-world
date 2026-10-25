'use client';

import { useQuery } from '@tanstack/react-query';
import { useTranslations } from 'next-intl';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { getEditorialSettings } from '@/lib/api/editorial';

export function CmsSettingsPage() {
  const t = useTranslations('cms');
  const settingsQuery = useQuery({
    queryKey: ['editorial-settings'],
    queryFn: () => getEditorialSettings(),
  });

  const settings = settingsQuery.data?.data;

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-semibold text-[var(--color-ink)]">{t('settingsTitle')}</h1>
        <p className="mt-1 text-[var(--color-ink-secondary)]">{t('settingsSubtitle')}</p>
      </div>

      {settingsQuery.isLoading ? (
        <Skeleton className="h-40" />
      ) : settings ? (
        <div className="grid gap-4 md:grid-cols-2">
          <Card>
            <CardHeader>
              <CardDescription>{t('editorDomains')}</CardDescription>
              <CardTitle className="text-base font-normal">
                {settings.editor_domains.join(', ')}
              </CardTitle>
            </CardHeader>
          </Card>
          <Card>
            <CardHeader>
              <CardDescription>{t('supportedLocales')}</CardDescription>
              <CardTitle className="text-base font-normal">
                {settings.supported_locales.join(', ')}
              </CardTitle>
            </CardHeader>
          </Card>
          <Card>
            <CardHeader>
              <CardDescription>{t('defaultLocale')}</CardDescription>
              <CardTitle className="text-base font-normal">{settings.default_locale}</CardTitle>
            </CardHeader>
          </Card>
          <Card>
            <CardHeader>
              <CardDescription>{t('blockTypes')}</CardDescription>
              <CardTitle className="text-base font-normal">
                {settings.block_types.join(', ')}
              </CardTitle>
            </CardHeader>
          </Card>
          {settings.maintenance_mode ? (
            <Card>
              <CardHeader>
                <CardDescription>{t('maintenanceNotice')}</CardDescription>
                <CardTitle className="text-base font-normal text-[var(--color-warning)]">
                  {t('maintenanceActive')}
                </CardTitle>
              </CardHeader>
            </Card>
          ) : null}
        </div>
      ) : null}
    </div>
  );
}
