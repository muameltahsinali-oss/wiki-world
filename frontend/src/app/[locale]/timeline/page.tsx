import { TimelinePage } from '@/components/ahlulbayt/timeline-page';
import { getTranslations } from 'next-intl/server';

export async function generateMetadata() {
  const t = await getTranslations('ahlulbayt.timeline');
  return { title: t('pageTitle') };
}

export default function TimelineRoutePage() {
  return <TimelinePage />;
}
