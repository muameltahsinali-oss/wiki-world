import { QuranVerseDetailPage } from '@/components/quran/quran-verse-detail-page';
import { getTranslations } from 'next-intl/server';

type PageProps = {
  params: Promise<{ locale: string; reference: string }>;
};

export async function generateMetadata({ params }: PageProps) {
  const { reference } = await params;
  const t = await getTranslations('quran');
  return { title: t('verseReference', { reference: decodeURIComponent(reference) }) };
}

export default async function QuranVersePage({ params }: PageProps) {
  const { reference } = await params;
  return <QuranVerseDetailPage reference={decodeURIComponent(reference)} />;
}
