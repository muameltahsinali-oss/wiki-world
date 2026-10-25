/** ISR: revalidate every hour (see lib/entity/page-config.ts). */
export const revalidate = 3600;

import { EntityPublicPage } from '@/components/entity/entity-public-page';
import { EntityJsonLd, buildEntityMetadata } from '@/components/entity/entity-seo';
import { ApiClientError } from '@/lib/api/client';
import { getPublicEntityByType } from '@/lib/api/public';
import { notFound } from 'next/navigation';

type PageProps = {
  params: Promise<{ locale: string; uuid: string }>;
};

export async function generateMetadata({ params }: PageProps) {
  const { locale, uuid } = await params;
  try {
    const response = await getPublicEntityByType(locale, 'concept', uuid);
    return buildEntityMetadata(response.data, locale);
  } catch {
    return {};
  }
}

export default async function ConceptPage({ params }: PageProps) {
  const { locale, uuid } = await params;
  let entity = null;

  try {
    entity = (await getPublicEntityByType(locale, 'concept', uuid)).data;
  } catch (error) {
    if (error instanceof ApiClientError && error.status === 404) {
      notFound();
    }
  }

  return (
    <>
      {entity ? <EntityJsonLd entity={entity} locale={locale} /> : null}
      <EntityPublicPage entityType="concept" uuid={uuid} />
    </>
  );
}
