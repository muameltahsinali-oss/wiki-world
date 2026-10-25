import { QuranSurahListPage } from '@/components/quran/quran-surah-list-page';
import { getTranslations } from 'next-intl/server';

export async function generateMetadata() {
  const t = await getTranslations('quran');
  return { title: t('surahList') };
}

export default function QuranSurahsPage() {
  return <QuranSurahListPage />;
}
