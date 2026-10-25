import { EntityBrowsePage } from '@/components/entity/entity-browse-page';
import { ENTITY_TYPE_ROUTES } from '@/lib/entity/types';
import { getTranslations } from 'next-intl/server';

export async function generateMetadata() {
  const t = await getTranslations('public.browse');
  return { title: t('sourcesTitle') };
}

export default function SourcesBrowsePage() {
  return (
    <EntityBrowsePage
      typeRoute={ENTITY_TYPE_ROUTES.source}
      entityType="source"
      titleKey="sourcesTitle"
    />
  );
}
