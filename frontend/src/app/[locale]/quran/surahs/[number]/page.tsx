import { QuranSurahReaderPage } from '@/components/quran/quran-surah-reader-page';
import { getTranslations } from 'next-intl/server';

type PageProps = {
  params: Promise<{ locale: string; number: string }>;
};

export async function generateMetadata({ params }: PageProps) {
  const { number } = await params;
  const t = await getTranslations('quran');
  return { title: t('surahNumber', { number }) };
}

export default async function QuranSurahPage({ params }: PageProps) {
  const { number } = await params;
  return <QuranSurahReaderPage number={Number(number)} />;
}
