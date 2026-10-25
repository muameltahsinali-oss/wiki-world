import dynamic from 'next/dynamic';
import { Skeleton } from '@/components/ui/skeleton';

const EntityEditorPage = dynamic(
  () => import('@/components/cms/entity-editor-page').then((m) => m.EntityEditorPage),
  { loading: () => <Skeleton className="h-[70vh] w-full" /> },
);

export default async function CmsEntityEditorPage({
  params,
}: {
  params: Promise<{ uuid: string }>;
}) {
  const { uuid } = await params;
  return <EntityEditorPage uuid={uuid} />;
}
