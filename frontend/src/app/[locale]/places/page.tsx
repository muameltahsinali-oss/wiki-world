import { EntityBrowsePage } from '@/components/entity/entity-browse-page';
import { ENTITY_TYPE_ROUTES } from '@/lib/entity/types';
import { getTranslations } from 'next-intl/server';

export async function generateMetadata() {
  const t = await getTranslations('public.browse');
  return { title: t('placesTitle') };
}

export default function PlacesBrowsePage() {
  return (
    <EntityBrowsePage
      typeRoute={ENTITY_TYPE_ROUTES.place}
      entityType="place"
      titleKey="placesTitle"
    />
  );
}
