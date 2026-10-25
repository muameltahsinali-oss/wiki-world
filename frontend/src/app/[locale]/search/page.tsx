import { getTranslations, setRequestLocale } from 'next-intl/server';
import { Suspense } from 'react';
import { SearchPage } from '@/components/search/search-page';
import { Skeleton } from '@/components/ui/skeleton';

type PageProps = {
  params: Promise<{ locale: string }>;
  searchParams: Promise<{ q?: string; type?: string; module?: string; page?: string }>;
};

export async function generateMetadata({ params }: PageProps) {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: 'search' });
  return {
    title: t('title'),
    description: t('subtitle'),
  };
}

export default async function SearchRoutePage({ params, searchParams }: PageProps) {
  const { locale } = await params;
  const query = await searchParams;
  setRequestLocale(locale);

  return (
    <Suspense
      fallback={
        <main className="mx-auto flex w-full max-w-7xl flex-col gap-4 px-4 py-10 sm:px-6">
          <Skeleton className="h-10 w-64" />
          <Skeleton className="h-12 w-full" />
          <Skeleton className="h-40 w-full" />
        </main>
      }
    >
      <SearchPage
        initialQuery={query.q ?? ''}
        initialType={query.type ?? ''}
        initialModule={query.module ?? ''}
      />
    </Suspense>
  );
}
