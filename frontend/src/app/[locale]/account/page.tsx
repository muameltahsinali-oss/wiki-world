'use client';

import { useLocale, useTranslations } from 'next-intl';
import { useEffect, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import { Tabs } from '@/components/ui/tabs';
import { Link, useRouter } from '@/i18n/navigation';
import { ApiClientError } from '@/lib/api/client';
import { getMe, updateMe, type UserProfile } from '@/lib/api/endpoints';
import {
  clearReadingHistory,
  getBookmarks,
  getPublicEntity,
  getReadingHistory,
  removeBookmark,
} from '@/lib/api/public';
import { entityPublicHref } from '@/lib/entity/types';
import type { EntityType } from '@/components/ui/badge';

export default function AccountPage() {
  const t = useTranslations('account');
  const errors = useTranslations('errors');
  const locale = useLocale();
  const router = useRouter();
  const queryClient = useQueryClient();
  const [profile, setProfile] = useState<UserProfile | null>(null);
  const [displayName, setDisplayName] = useState('');
  const [preferredLocale, setPreferredLocale] = useState<'ar' | 'en'>('ar');
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [saved, setSaved] = useState(false);

  useEffect(() => {
    let active = true;

    getMe(locale)
      .then((response) => {
        if (!active) return;
        setProfile(response.data);
        setDisplayName(response.data.display_name ?? '');
        setPreferredLocale((response.data.preferred_locale as 'ar' | 'en') ?? 'ar');
      })
      .catch((err) => {
        if (!active) return;
        if (err instanceof ApiClientError && err.status === 401) {
          router.push('/login');
          return;
        }
        setError(errors('generic'));
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [locale, router, errors]);

  const bookmarksQuery = useQuery({
    queryKey: ['bookmarks', locale],
    queryFn: () => getBookmarks(locale),
    enabled: !loading && Boolean(profile),
  });

  const historyQuery = useQuery({
    queryKey: ['reading-history', locale],
    queryFn: () => getReadingHistory(locale),
    enabled: !loading && Boolean(profile),
  });

  const removeBookmarkMutation = useMutation({
    mutationFn: (entityUuid: string) => removeBookmark(entityUuid, locale),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bookmarks', locale] });
    },
  });

  const clearHistoryMutation = useMutation({
    mutationFn: () => clearReadingHistory(locale),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['reading-history', locale] });
    },
  });

  const handleSave = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setSaving(true);
    setError(null);
    setSaved(false);

    try {
      const response = await updateMe(
        {
          display_name: displayName,
          preferred_locale: preferredLocale,
        },
        locale,
      );
      setProfile(response.data);
      setSaved(true);
    } catch (err) {
      if (err instanceof ApiClientError && err.status === 401) {
        router.push('/login');
        return;
      }
      setError(errors('generic'));
    } finally {
      setSaving(false);
    }
  };

  return (
    <main className="mx-auto flex w-full max-w-2xl flex-col gap-6 px-4 py-12 sm:px-6">
      <Card>
        <CardHeader>
          <CardTitle>{t('title')}</CardTitle>
          <CardDescription>{t('subtitle')}</CardDescription>
        </CardHeader>
        <CardContent>
          {loading ? (
            <div className="flex flex-col gap-4">
              <Skeleton className="h-10 w-full" />
              <Skeleton className="h-10 w-full" />
              <Skeleton className="h-10 w-32" />
            </div>
          ) : (
            <form onSubmit={handleSave} className="flex flex-col gap-4">
              {error ? <Alert variant="error">{error}</Alert> : null}
              {saved ? <Alert variant="success">{t('saved')}</Alert> : null}
              <Input label={t('emailLabel')} value={profile?.email ?? ''} disabled readOnly />
              <Input
                label={t('displayNameLabel')}
                value={displayName}
                onChange={(event) => setDisplayName(event.target.value)}
              />
              <div className="flex flex-col gap-1.5">
                <label htmlFor="preferred-locale" className="text-sm font-medium text-[var(--color-ink)]">
                  {t('localeLabel')}
                </label>
                <select
                  id="preferred-locale"
                  value={preferredLocale}
                  onChange={(event) => setPreferredLocale(event.target.value as 'ar' | 'en')}
                  className="flex h-10 w-full rounded-md border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] px-3 text-sm"
                >
                  <option value="ar">العربية</option>
                  <option value="en">English</option>
                </select>
              </div>
              {profile?.roles?.length ? (
                <p className="text-sm text-[var(--color-ink-secondary)]">
                  {t('rolesLabel')}: {profile.roles.join(', ')}
                </p>
              ) : null}
              <Button type="submit" loading={saving}>
                {t('saveChanges')}
              </Button>
            </form>
          )}
        </CardContent>
      </Card>

      {!loading && profile ? (
        <Tabs
          defaultTab="bookmarks"
          items={[
            {
              id: 'bookmarks',
              label: t('bookmarksTab'),
              content: (
                <Card>
                  <CardHeader>
                    <CardTitle className="text-base">{t('bookmarksTitle')}</CardTitle>
                    <CardDescription>{t('bookmarksSubtitle')}</CardDescription>
                  </CardHeader>
                  <CardContent>
                    {bookmarksQuery.isLoading ? (
                      <Skeleton className="h-20 w-full" />
                    ) : bookmarksQuery.data?.data.length ? (
                      <ul className="flex flex-col gap-2">
                        {bookmarksQuery.data.data.map((bookmark) => (
                          <BookmarkItem
                            key={bookmark.entity_uuid}
                            entityUuid={bookmark.entity_uuid}
                            locale={locale}
                            onRemove={() => removeBookmarkMutation.mutate(bookmark.entity_uuid)}
                            removing={removeBookmarkMutation.isPending}
                          />
                        ))}
                      </ul>
                    ) : (
                      <p className="text-sm text-[var(--color-ink-secondary)]">{t('noBookmarks')}</p>
                    )}
                  </CardContent>
                </Card>
              ),
            },
            {
              id: 'history',
              label: t('historyTab'),
              content: (
                <Card>
                  <CardHeader className="flex flex-row items-start justify-between gap-4">
                    <div>
                      <CardTitle className="text-base">{t('historyTitle')}</CardTitle>
                      <CardDescription>{t('historySubtitle')}</CardDescription>
                    </div>
                    {historyQuery.data?.data.length ? (
                      <Button
                        type="button"
                        variant="secondary"
                        size="sm"
                        loading={clearHistoryMutation.isPending}
                        onClick={() => clearHistoryMutation.mutate()}
                      >
                        {t('clearHistory')}
                      </Button>
                    ) : null}
                  </CardHeader>
                  <CardContent>
                    {historyQuery.isLoading ? (
                      <Skeleton className="h-20 w-full" />
                    ) : historyQuery.data?.data.length ? (
                      <ul className="flex flex-col gap-2">
                        {historyQuery.data.data.map((entry) => (
                          <HistoryItem
                            key={`${entry.entity_uuid}-${entry.viewed_at}`}
                            entityUuid={entry.entity_uuid}
                            viewedAt={entry.viewed_at}
                            locale={locale}
                          />
                        ))}
                      </ul>
                    ) : (
                      <p className="text-sm text-[var(--color-ink-secondary)]">{t('noHistory')}</p>
                    )}
                  </CardContent>
                </Card>
              ),
            },
          ]}
        />
      ) : null}
    </main>
  );
}

function BookmarkItem({
  entityUuid,
  locale,
  onRemove,
  removing,
}: {
  entityUuid: string;
  locale: string;
  onRemove: () => void;
  removing: boolean;
}) {
  const t = useTranslations('account');
  const entityQuery = useQuery({
    queryKey: ['bookmark-entity', entityUuid, locale],
    queryFn: () => getPublicEntity(locale, entityUuid),
    retry: false,
  });

  const entity = entityQuery.data?.data;
  const href = entity ? entityPublicHref(entity.type, entity.uuid) : '#';

  return (
    <li className="flex items-center justify-between gap-3 rounded-lg border border-[var(--color-border-default)] px-3 py-2">
      <Link href={href} className="text-sm font-medium text-[var(--color-scholar)] hover:underline">
        {entity?.title ?? entityUuid}
      </Link>
      <Button type="button" variant="secondary" size="sm" loading={removing} onClick={onRemove}>
        {t('removeBookmark')}
      </Button>
    </li>
  );
}

function HistoryItem({
  entityUuid,
  viewedAt,
  locale,
}: {
  entityUuid: string;
  viewedAt: string;
  locale: string;
}) {
  const entityQuery = useQuery({
    queryKey: ['history-entity', entityUuid, locale],
    queryFn: () => getPublicEntity(locale, entityUuid),
    retry: false,
  });

  const entity = entityQuery.data?.data;
  const href = entity ? entityPublicHref(entity.type as EntityType, entity.uuid) : '#';

  return (
    <li className="flex items-center justify-between gap-3 rounded-lg border border-[var(--color-border-default)] px-3 py-2 text-sm">
      <Link href={href} className="font-medium text-[var(--color-scholar)] hover:underline">
        {entity?.title ?? entityUuid}
      </Link>
      <time className="text-[var(--color-ink-tertiary)]" dateTime={viewedAt}>
        {new Date(viewedAt).toLocaleString(locale)}
      </time>
    </li>
  );
}
