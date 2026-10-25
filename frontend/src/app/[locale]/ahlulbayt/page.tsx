import { AhlulbaytLandingPage } from '@/components/ahlulbayt/ahlulbayt-landing-page';
import { getTranslations } from 'next-intl/server';

export async function generateMetadata() {
  const t = await getTranslations('ahlulbayt');
  return { title: t('title'), description: t('subtitle') };
}

export default function AhlulbaytPage() {
  return <AhlulbaytLandingPage />;
}
