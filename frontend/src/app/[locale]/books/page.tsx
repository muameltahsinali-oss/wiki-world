import { EntityBrowsePage } from '@/components/entity/entity-browse-page';
import { ENTITY_TYPE_ROUTES } from '@/lib/entity/types';
import { getTranslations } from 'next-intl/server';

export async function generateMetadata() {
  const t = await getTranslations('public.browse');
  return { title: t('booksTitle') };
}

export default function BooksBrowsePage() {
  return (
    <EntityBrowsePage
      typeRoute={ENTITY_TYPE_ROUTES.book}
      entityType="book"
      titleKey="booksTitle"
    />
  );
}
