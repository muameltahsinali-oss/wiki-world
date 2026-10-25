import { QuranLandingPage } from '@/components/quran/quran-landing-page';
import { getTranslations } from 'next-intl/server';

export async function generateMetadata() {
  const t = await getTranslations('quran');
  return { title: t('title'), description: t('subtitle') };
}

export default function QuranPage() {
  return <QuranLandingPage />;
}
