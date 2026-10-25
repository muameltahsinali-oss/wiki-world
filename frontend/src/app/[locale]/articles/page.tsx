import { EntityBrowsePage } from '@/components/entity/entity-browse-page';
import { ENTITY_TYPE_ROUTES } from '@/lib/entity/types';
import { getTranslations } from 'next-intl/server';

export async function generateMetadata() {
  const t = await getTranslations('public.browse');
  return { title: t('articlesTitle') };
}

export default function ArticlesBrowsePage() {
  return (
    <EntityBrowsePage
      typeRoute={ENTITY_TYPE_ROUTES.article}
      entityType="article"
      titleKey="articlesTitle"
    />
  );
}
