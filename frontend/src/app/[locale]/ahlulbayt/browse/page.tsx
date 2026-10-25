import { AhlulbaytBrowsePage } from '@/components/ahlulbayt/ahlulbayt-browse-page';
import { getTranslations } from 'next-intl/server';

export async function generateMetadata() {
  const t = await getTranslations('ahlulbayt.browse');
  return { title: t('title') };
}

export default function AhlulbaytBrowseRoutePage() {
  return <AhlulbaytBrowsePage />;
}
